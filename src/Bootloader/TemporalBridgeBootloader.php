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
use Spiral\Core\FactoryInterface;
use Spiral\RoadRunnerBridge\Bootloader\RoadRunnerBootloader;
use Spiral\TemporalBridge\Commands;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\Preset\PresetRegistry;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\WorkerFactory;
use Spiral\TemporalBridge\WorkerFactoryInterface;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Spiral\TemporalBridge\Workflow\WorkflowManager;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Spiral\TemporalBridge\WorkflowPresetLocator;
use Spiral\TemporalBridge\WorkflowPresetLocatorInterface;
use Spiral\Tokenizer\ClassesInterface;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Internal\Interceptor\PipelineProvider;
use Temporal\Worker\Transport\Goridge;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory as TemporalWorkerFactory;

class TemporalBridgeBootloader extends Bootloader
{
    protected const SINGLETONS = [
        WorkflowPresetLocatorInterface::class => [self::class, 'initWorkflowPresetLocator'],
        WorkflowManagerInterface::class => WorkflowManager::class,
        TemporalWorkerFactoryInterface::class => [self::class, 'initWorkerFactory'],
        DeclarationLocatorInterface::class => [self::class, 'initDeclarationLocator'],
        WorkflowClientInterface::class => [self::class, 'initWorkflowClient'],
        WorkersRegistryInterface::class => WorkersRegistry::class,
        PresetRegistryInterface::class => PresetRegistry::class,
        DataConverterInterface::class => [self::class, 'initDataConverter'],
        WorkerFactoryInterface::class => WorkerFactory::class,
        PipelineProvider::class => SimplePipelineProvider::class,
    ];

    protected const DEPENDENCIES = [
        ConsoleBootloader::class,
        RoadRunnerBootloader::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(
        AbstractKernel $kernel,
        EnvironmentInterface $env,
        ConsoleBootloader $console,
        FactoryInterface $factory
    ): void {
        $this->initConfig($env);

        $kernel->addDispatcher($factory->make(Dispatcher::class));

        $console->addCommand(Commands\MakeWorkflowCommand::class);
        $console->addCommand(Commands\MakePresetCommand::class);
        $console->addCommand(Commands\PresetListCommand::class);
    }

    public function addWorkerOptions(string $worker, WorkerOptions $options): void
    {
        $this->config->modify(TemporalConfig::CONFIG, new Append('workers', $worker, $options));
    }

    protected function initWorkflowPresetLocator(
        FactoryInterface $factory,
        ClassesInterface $classes
    ): WorkflowPresetLocatorInterface {
        return new WorkflowPresetLocator(
            factory: $factory,
            classes: $classes,
            reader: new AttributeReader()
        );
    }

    protected function initConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            TemporalConfig::CONFIG,
            [
                'address' => $env->get('TEMPORAL_ADDRESS', '127.0.0.1:7233'),
                'namespace' => 'App\\Workflow',
                'defaultWorker' => (string)$env->get('TEMPORAL_TASK_QUEUE', TemporalWorkerFactory::DEFAULT_TASK_QUEUE),
                'workers' => [],
            ]
        );
    }

    protected function initWorkflowClient(
        TemporalConfig $config,
        DataConverterInterface $dataConverter
    ): WorkflowClientInterface {
        return WorkflowClient::create(
            serviceClient: ServiceClient::create($config->getAddress()),
            options: (new ClientOptions())->withNamespace($config->getTemporalNamespace()),
            converter: $dataConverter
        );
    }

    protected function initDataConverter(): DataConverterInterface
    {
        return DataConverter::createDefault();
    }

    protected function initWorkerFactory(
        DataConverterInterface $dataConverter
    ): TemporalWorkerFactoryInterface {
        return new TemporalWorkerFactory(
            dataConverter: $dataConverter,
            rpc: Goridge::create()
        );
    }

    protected function initDeclarationLocator(
        ClassesInterface $classes
    ): DeclarationLocatorInterface {
        return new \Spiral\TemporalBridge\DeclarationLocator(
            classes: $classes,
            reader: new AttributeReader()
        );
    }
}
