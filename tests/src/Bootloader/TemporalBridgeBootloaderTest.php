<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Bootloader;

use Mockery as m;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\WorkerFactory;
use Spiral\TemporalBridge\WorkerFactoryInterface;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\GRPC\ServiceClientInterface;
use Temporal\Client\ScheduleClient;
use Temporal\Client\ScheduleClientInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Interceptor\PipelineProvider;
use Temporal\Internal\Interceptor\Interceptor;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory as TemporalWorkerFactory;

class TemporalBridgeBootloaderTest extends TestCase
{
    public function testServiceClient(): void
    {
        $this->assertContainerBoundAsSingleton(
            ServiceClientInterface::class,
            ServiceClient::class,
        );
    }

    public function testScheduleClient(): void
    {
        $this->assertContainerBoundAsSingleton(
            ScheduleClientInterface::class,
            ScheduleClient::class,
        );
    }

    public function testTemporalWorkerFactory(): void
    {
        $this->assertContainerBoundAsSingleton(
            TemporalWorkerFactoryInterface::class,
            TemporalWorkerFactory::class,
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
            DataConverter::class,
        );
    }

    public function testDeclarationLocator(): void
    {
        $this->assertContainerBoundAsSingleton(
            DeclarationLocatorInterface::class,
            DeclarationLocator::class,
        );
    }

    public function testWorkflowClient(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkflowClientInterface::class,
            WorkflowClient::class,
        );
    }

    public function testWorkersRegistry(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkersRegistryInterface::class,
            WorkersRegistry::class,
        );
    }

    public function testPipelineProvider(): void
    {
        $this->assertContainerBound(
            PipelineProvider::class,
            SimplePipelineProvider::class,
        );
    }

    public function testAddWorkerOptions(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TemporalConfig::CONFIG, ['workers' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $this->getContainer());
        $bootloader->addWorkerOptions('first', $first = WorkerOptions::new());
        $bootloader->addWorkerOptions('second', $second = WorkerOptions::new());

        $this->assertSame(
            ['first' => $first, 'second' => $second],
            $configs->getConfig(TemporalConfig::CONFIG)['workers'],
        );
    }

    public function testAddInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $this->getContainer());

        $bootloader->addInterceptor($iterceptor = m::mock(Interceptor::class));

        $this->assertSame(
            [$iterceptor],
            $configs->getConfig(TemporalConfig::CONFIG)['interceptors'],
        );
    }

    public function testStringableInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $factory = m::mock(FactoryInterface::class));

        $factory->shouldReceive('make')->with('foo')->andReturn($iterceptor = m::mock(Interceptor::class));

        $bootloader->addInterceptor('foo');

        $this->assertSame(
            [$iterceptor],
            $configs->getConfig(TemporalConfig::CONFIG)['interceptors'],
        );
    }

    public function testAutowireInterceptor(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $factory = m::mock(FactoryInterface::class));

        $factory->shouldReceive('make')->with('foo', ['bar' => 'baz'])->andReturn($iterceptor = m::mock(Interceptor::class));

        $bootloader->addInterceptor(new Autowire('foo', ['bar' => 'baz']));

        $this->assertSame(
            [$iterceptor],
            $configs->getConfig(TemporalConfig::CONFIG)['interceptors'],
        );
    }

    public function testInvalidInterceptor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Interceptor must be an instance of `Temporal\Internal\Interceptor\Interceptor`, `stdClass` given.');

        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $factory = m::mock(FactoryInterface::class));

        $factory->shouldReceive('make')->with('foo')->andReturn(new \StdClass());

        $bootloader->addInterceptor('foo');
    }
}
