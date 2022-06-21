<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Bootloader;

use Spiral\Attributes\ReaderInterface;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Preset\PresetRegistry;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\Workflow\WorkflowManager;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Spiral\TemporalBridge\WorkflowPresetLocator;
use Spiral\TemporalBridge\WorkflowPresetLocatorInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Worker\WorkerFactoryInterface;
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
}
