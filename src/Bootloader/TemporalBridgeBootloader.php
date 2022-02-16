<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Bootloader;

use Spiral\Attributes\AttributeReader;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;
use Spiral\Config\ConfiguratorInterface;
use Spiral\TemporalBridge\Commands;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Workflow\WorkflowManager;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Spiral\Tokenizer\ClassesInterface;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\WorkerFactory;

class TemporalBridgeBootloader extends Bootloader
{
    protected const BINDINGS = [];
    protected const SINGLETONS = [
        WorkflowManagerInterface::class => WorkflowManager::class,
        WorkerFactoryInterface::class => [self::class, 'initWorkerFactory'],
        DeclarationLocatorInterface::class => [self::class, 'initDeclarationLocator'],
        WorkflowClientInterface::class => [self::class, 'initWorkflowClient'],
    ];

    protected const DEPENDENCIES = [
        ConsoleBootloader::class
    ];

    public function __construct(private ConfiguratorInterface $config)
    {
    }

    public function boot(
        AbstractKernel $kernel,
        EnvironmentInterface $env,
        ConsoleBootloader $console,
        Dispatcher $dispatcher
    ): void {
        $this->initConfig($env);

        $kernel->addDispatcher($dispatcher);
        $console->addCommand(Commands\MakeWorkflowCommand::class);
    }

    public function start(Container $container): void
    {
    }

    private function initConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            TemporalConfig::CONFIG,
            [
                'address' => $env->get('TEMPORAL_ADDRESS', '127.0.0.1:7233'),
                'namespace' => 'App\\Workflow'
            ]
        );
    }

    private function initWorkflowClient(TemporalConfig $config): WorkflowClientInterface
    {
        return WorkflowClient::create(
            ServiceClient::create($config->getAddress())
        );
    }

    private function initWorkerFactory(): WorkerFactoryInterface
    {
        return WorkerFactory::create();
    }

    private function initDeclarationLocator(ClassesInterface $classes):DeclarationLocatorInterface
    {
        return new \Spiral\TemporalBridge\DeclarationLocator(
            $classes,
            new AttributeReader()
        );
    }
}
