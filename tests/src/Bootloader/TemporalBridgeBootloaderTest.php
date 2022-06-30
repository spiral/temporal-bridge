<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Bootloader;

use Spiral\Attributes\ReaderInterface;
use Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Preset\PresetRegistry;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Spiral\TemporalBridge\Workflow\WorkflowManager;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Spiral\TemporalBridge\WorkflowPresetLocator;
use Spiral\TemporalBridge\WorkflowPresetLocatorInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory;

class TemporalBridgeBootloaderTest extends TestCase
{
    public function testWorkflowPresetLocator()
    {
        $this->assertContainerBoundAsSingleton(
            WorkflowPresetLocatorInterface::class,
            WorkflowPresetLocator::class
        );
    }

    public function testWorkflowManager()
    {
        $this->mockContainer(ReaderInterface::class);

        $this->assertContainerBoundAsSingleton(
            WorkflowManagerInterface::class,
            WorkflowManager::class
        );
    }

    public function testWorkerFactory()
    {
        $this->assertContainerBoundAsSingleton(
            WorkerFactoryInterface::class,
            WorkerFactory::class
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

    public function testPresetRegistry()
    {
        $this->assertContainerBoundAsSingleton(
            PresetRegistryInterface::class,
            PresetRegistry::class
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
        $bootloader = $this->getContainer()->get(TemporalBridgeBootloader::class);

        $bootloader->addWorkerOptions('first', WorkerOptions::new());
        $bootloader->addWorkerOptions('second', WorkerOptions::new());

        $this->assertEquals(
            ['first' => WorkerOptions::new(), 'second' => WorkerOptions::new()],
            $this->getConfig(TemporalConfig::CONFIG)['workers']
        );
    }
}
