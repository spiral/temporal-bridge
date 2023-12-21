<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Bootloader;

use Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory;

class TemporalBridgeBootloaderTest extends TestCase
{
    public function testWorkerFactory()
    {
        $this->assertContainerBoundAsSingleton(
            WorkerFactoryInterface::class,
            WorkerFactory::class
        );
    }

    public function testDataConverter()
    {
        $this->assertContainerBoundAsSingleton(
            DataConverterInterface::class,
            DataConverter::class
        );
    }

    public function testDeclarationLocator()
    {
        $this->assertContainerBoundAsSingleton(
            DeclarationLocatorInterface::class,
            DeclarationLocator::class
        );
    }

    public function testWorkflowClient()
    {
        $this->assertContainerBoundAsSingleton(
            WorkflowClientInterface::class,
            WorkflowClient::class
        );
    }

    public function testWorkersRegistry(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkersRegistryInterface::class,
            WorkersRegistry::class
        );
    }

    public function testAddWorkerOptions(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TemporalConfig::CONFIG, ['workers' => []]);

        $bootloader = new TemporalBridgeBootloader($configs);
        $bootloader->addWorkerOptions('first', $first = WorkerOptions::new());
        $bootloader->addWorkerOptions('second', $second = WorkerOptions::new());

        $this->assertSame(
            ['first' => $first, 'second' => $second],
            $configs->getConfig(TemporalConfig::CONFIG)['workers']
        );
    }
}
