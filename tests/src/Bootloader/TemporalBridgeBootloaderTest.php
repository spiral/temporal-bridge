<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Bootloader;

use Spiral\Attributes\ReaderInterface;
use Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Preset\PresetRegistry;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\WorkerFactory;
use Spiral\TemporalBridge\WorkerFactoryInterface;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Spiral\TemporalBridge\Workflow\WorkflowManager;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Spiral\TemporalBridge\WorkflowPresetLocator;
use Spiral\TemporalBridge\WorkflowPresetLocatorInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Internal\Interceptor\PipelineProvider;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory as TemporalWorkerFactory;

class TemporalBridgeBootloaderTest extends TestCase
{
    public function testWorkflowPresetLocator(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkflowPresetLocatorInterface::class,
            WorkflowPresetLocator::class
        );
    }

    public function testWorkflowManager(): void
    {
        $this->mockContainer(ReaderInterface::class);

        $this->assertContainerBoundAsSingleton(
            WorkflowManagerInterface::class,
            WorkflowManager::class
        );
    }

    public function testTemporalWorkerFactory(): void
    {
        $this->assertContainerBoundAsSingleton(
            TemporalWorkerFactoryInterface::class,
            TemporalWorkerFactory::class
        );
    }

    public function testWorkerFactory(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkerFactoryInterface::class,
            WorkerFactory::class
        );
    }

    public function testDataConverter(): void
    {
        $this->assertContainerBoundAsSingleton(
            DataConverterInterface::class,
            DataConverter::class
        );
    }

    public function testDeclarationLocator(): void
    {
        $this->assertContainerBoundAsSingleton(
            DeclarationLocatorInterface::class,
            DeclarationLocator::class
        );
    }

    public function testWorkflowClient(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkflowClientInterface::class,
            WorkflowClient::class
        );
    }

    public function testPresetRegistry(): void
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

    public function testPipelineProvider(): void
    {
        $this->assertContainerBound(
            PipelineProvider::class,
            SimplePipelineProvider::class
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
