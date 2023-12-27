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
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\WorkerFactory;
use Spiral\TemporalBridge\WorkerFactoryInterface;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Spiral\Tokenizer\ClassesInterface;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Interceptor\PipelineProvider;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Internal\Interceptor\Interceptor;
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
    public function defineDependencies(): array
    {
        return [
            ConsoleBootloader::class,
            RoadRunnerBootloader::class,
            ScaffolderBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        return [
            TemporalWorkerFactoryInterface::class => [self::class, 'initWorkerFactory'],
            WorkerFactoryInterface::class => WorkerFactory::class,
            DeclarationLocatorInterface::class => [self::class, 'initDeclarationLocator'],
            WorkflowClientInterface::class => [self::class, 'initWorkflowClient'],
            WorkersRegistryInterface::class => WorkersRegistry::class,
            ScheduleClientInterface::class => [self::class, 'initScheduleClient'],
            DataConverterInterface::class => [self::class, 'initDataConverter'],
            PipelineProvider::class => [self::class, 'initPipelineProvider'],
            ServiceClientInterface::class => [self::class, 'initServiceClient'],
        ];
    }

    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly FactoryInterface $factory,
    ) {
    }

    public function init(
        AbstractKernel $kernel,
        EnvironmentInterface $env,
        Dispatcher $dispatcher,
    ): void {
        $this->initConfig($env);

        $kernel->addDispatcher($dispatcher);
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
                'address' => $env->get('TEMPORAL_ADDRESS', '127.0.0.1:7233'),
                'namespace' => 'App\\Endpoint\\Temporal\\Workflow',
                'defaultWorker' => (string)$env->get(
                    'TEMPORAL_TASK_QUEUE',
                    TemporalWorkerFactoryInterface::DEFAULT_TASK_QUEUE,
                ),
                'workers' => [],
                'clientOptions' => null,
            ],
        );
    }

    protected function initWorkflowClient(
        TemporalConfig $config,
        DataConverterInterface $dataConverter,
        PipelineProvider $pipelineProvider,
        ServiceClientInterface $serviceClient,
    ): WorkflowClientInterface {
        return new WorkflowClient(
            serviceClient: $serviceClient,
            options: $config->getClientOptions(),
            converter: $dataConverter,
            interceptorProvider: $pipelineProvider,
        );
    }

    protected function initDataConverter(): DataConverterInterface
    {
        return DataConverter::createDefault();
    }

    protected function initWorkerFactory(DataConverterInterface $dataConverter,): TemporalWorkerFactoryInterface
    {
        return new TemporalWorkerFactory(
            dataConverter: $dataConverter,
            rpc: Goridge::create(),
        );
    }

    protected function initDeclarationLocator(ClassesInterface $classes,): DeclarationLocatorInterface
    {
        return new DeclarationLocator(
            classes: $classes,
            reader: new AttributeReader(),
        );
    }

    protected function initPipelineProvider(TemporalConfig $config, FactoryInterface $factory): PipelineProvider
    {
        /** @var Interceptor[] $interceptors */
        $interceptors = \array_map(
            static fn(mixed $interceptor) => match (true) {
                \is_string($interceptor) => $factory->make($interceptor),
                $interceptor instanceof Autowire => $interceptor->resolve($factory),
                default => $interceptor
            },
            $config->getInterceptors(),
        );

        return new SimplePipelineProvider($interceptors);
    }

    protected function initServiceClient(TemporalConfig $config): ServiceClientInterface
    {
        return ServiceClient::create($config->getAddress());
    }

    protected function initScheduleClient(
        TemporalConfig $config,
        DataConverterInterface $dataConverter,
        ServiceClientInterface $serviceClient,
    ): ScheduleClientInterface {
        return new ScheduleClient(
            serviceClient: $serviceClient,
            options: $config->getClientOptions(),
            converter: $dataConverter,
        );
    }
}
