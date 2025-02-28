<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Bootloader;

use Spiral\Attributes\AttributeReader;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\RoadRunnerBridge\Bootloader\RoadRunnerBootloader;
use Spiral\TemporalBridge\Commands;
use Spiral\TemporalBridge\Config\ClientConfig;
use Spiral\TemporalBridge\Config\ConnectionConfig;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\DeclarationRegistryInterface;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\WorkerFactory;
use Spiral\TemporalBridge\WorkerFactoryInterface;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Interceptor\PipelineProvider;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Internal\Interceptor\Interceptor;
use Temporal\Worker\ServiceCredentials;
use Temporal\Worker\Transport\Goridge;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory as TemporalWorkerFactory;
use Temporal\Client\ScheduleClient;
use Temporal\Client\ScheduleClientInterface;
use Temporal\Client\GRPC\ServiceClientInterface;

/**
 * @psalm-import-type TInterceptor from TemporalConfig
 */
class TemporalBridgeBootloader extends Bootloader
{
    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly FactoryInterface $factory,
    ) {}

    public function defineDependencies(): array
    {
        return [
            RoadRunnerBootloader::class,
            ScaffolderBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        return [
            TemporalWorkerFactoryInterface::class => static fn(
                DataConverterInterface $dataConverter,
                ServiceCredentials $credentials,
            ): TemporalWorkerFactoryInterface => new TemporalWorkerFactory(
                dataConverter: $dataConverter,
                rpc: Goridge::create(),
                credentials: $credentials,
            ),
            ServiceCredentials::class => static function (TemporalConfig $config): ServiceCredentials {
                $client = $config->getClientConfig($config->getDefaultClient());
                $result = ServiceCredentials::create();
                // Set the API key if it is provided.
                $token = $client->connection->authToken;
                $token === null or $result = $result->withApiKey((string) $token);

                return $result;
            },
            WorkerFactoryInterface::class => WorkerFactory::class,
            DeclarationLocator::class => static fn(): DeclarationLocator => new DeclarationLocator(
                reader: new AttributeReader(),
            ),
            DeclarationLocatorInterface::class => DeclarationLocator::class,
            DeclarationRegistryInterface::class => DeclarationLocator::class,

            WorkflowClientInterface::class => static fn(
                TemporalConfig $config,
                DataConverterInterface $dataConverter,
                PipelineProvider $pipelineProvider,
                ServiceClientInterface $serviceClient,
            ): WorkflowClientInterface => new WorkflowClient(
                serviceClient: $serviceClient,
                options: $config->getClientOptions(),
                converter: $dataConverter,
                interceptorProvider: $pipelineProvider,
            ),
            WorkersRegistryInterface::class => WorkersRegistry::class,

            ScheduleClientInterface::class => static fn(
                TemporalConfig $config,
                DataConverterInterface $dataConverter,
                ServiceClientInterface $serviceClient,
            ): ScheduleClientInterface => new ScheduleClient(
                serviceClient: $serviceClient,
                options: $config->getClientOptions(),
                converter: $dataConverter,
            ),

            DataConverterInterface::class => static fn() => DataConverter::createDefault(),
            PipelineProvider::class => [self::class, 'initPipelineProvider'],
            ServiceClientInterface::class => [self::class, 'initServiceClient'],
        ];
    }

    public function init(
        AbstractKernel $kernel,
        EnvironmentInterface $env,
        ConsoleBootloader $console,
        TokenizerListenerRegistryInterface $tokenizer,
        DeclarationLocator $locator,
    ): void {
        $this->initConfig($env);
        $tokenizer->addListener($locator);
        $console->addCommand(Commands\InfoCommand::class);
        $kernel->addDispatcher(Dispatcher::class);
    }

    public function addWorkerOptions(string $worker, WorkerOptions $options): void
    {
        $this->config->modify(TemporalConfig::CONFIG, new Append('workers', $worker, $options));
    }

    /**
     * Register a new Temporal interceptor.
     *
     * @param TInterceptor $interceptor
     */
    public function addInterceptor(string|Interceptor|Autowire $interceptor): void
    {
        if (\is_string($interceptor)) {
            $interceptor = $this->factory->make($interceptor);
        } elseif ($interceptor instanceof Autowire) {
            $interceptor = $interceptor->resolve($this->factory);
        }

        if (!$interceptor instanceof Interceptor) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Interceptor must be an instance of `%s`, `%s` given.',
                    Interceptor::class,
                    \get_class($interceptor),
                ),
            );
        }

        $this->config->modify(TemporalConfig::CONFIG, new Append('interceptors', null, $interceptor));
    }

    protected function initConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            TemporalConfig::CONFIG,
            [
                'client' => $env->get('TEMPORAL_CONNECTION', 'default'),
                'clients' => [
                    'default' => new ClientConfig(
                        connection: new ConnectionConfig(
                            address: $env->get('TEMPORAL_ADDRESS', '127.0.0.1:7233'),
                        ),
                    ),
                ],
                'defaultWorker' => (string) $env->get(
                    'TEMPORAL_TASK_QUEUE',
                    TemporalWorkerFactoryInterface::DEFAULT_TASK_QUEUE,
                ),
                'workers' => [],
            ],
        );
    }

    protected function initServiceClient(TemporalConfig $config): ServiceClientInterface
    {
        $client = $config->getClientConfig($config->getDefaultClient());
        $connection = $client->connection;

        $isSecure = $connection->isSecure() || $connection->authToken !== null;

        $result = $isSecure
            ? ServiceClient::createSSL(
                address: $connection->address,
                crt: $connection->tls?->rootCerts,
                clientKey: $connection->tls?->privateKey,
                clientPem: $connection->tls?->certChain,
                overrideServerName: $connection->tls?->serverName,
            )
            : ServiceClient::create(address: $connection->address);

        $connection->authToken === null or $result = $result->withAuthKey($connection->authToken);
        return $result->withContext($client->context);
    }

    protected function initPipelineProvider(TemporalConfig $config, FactoryInterface $factory): PipelineProvider
    {
        /** @var Interceptor[] $interceptors */
        $interceptors = \array_map(
            static fn(mixed $interceptor) => match (true) {
                \is_string($interceptor) => $factory->make($interceptor),
                $interceptor instanceof Autowire => $interceptor->resolve($factory),
                default => $interceptor,
            },
            $config->getInterceptors(),
        );

        return new SimplePipelineProvider($interceptors);
    }
}
