<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Preset;

use Spiral\Attributes\AttributeReader;
use Spiral\Boot\FinalizerInterface;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Tests\App\SomeActivity;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\Tests\App\WithoutAttribute;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\WorkersRegistry;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Worker\Transport\RPCConnectionInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory;

final class WorkersRegistryTest extends TestCase
{
    public function testGetWorker(): void
    {
        $options = WorkerOptions::new();
        $options->enableSessionWorker = true;
        $factory =  new WorkerFactory(
            $this->createMock(DataConverterInterface::class),
            $this->createMock(RPCConnectionInterface::class)
        );

        $registry = new WorkersRegistry(
            $factory,
            new AttributeReader(),
            $this->createMock(FinalizerInterface::class),
            new TemporalConfig(['workers' => ['worker1' => $options]])
        );

        $worker = $registry->get(new \ReflectionClass(SomeActivity::class));
        $this->assertInstanceOf(WorkerInterface::class, $worker);
        $this->assertTrue($worker->getOptions()->enableSessionWorker);

        $worker = $registry->get(new \ReflectionClass(SomeWorkflow::class));
        $this->assertInstanceOf(WorkerInterface::class, $worker);
        $this->assertFalse($worker->getOptions()->enableSessionWorker);
    }

    public function testHasWorker(): void
    {
        $registry = new WorkersRegistry(
            $this->createMock(WorkerFactoryInterface::class),
            new AttributeReader(),
            $this->createMock(FinalizerInterface::class),
            new TemporalConfig()
        );
        $method = new \ReflectionMethod($registry, 'hasWorker');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($registry, 'default'));

        $registry->get(new \ReflectionClass(WithoutAttribute::class));
        $this->assertTrue($method->invoke($registry, 'default'));
    }

    /** @dataProvider resolveNameDataProvider */
    public function testResolveName(\ReflectionClass $class, string $expectedName): void
    {
        $registry = new WorkersRegistry(
            $this->createMock(WorkerFactoryInterface::class),
            new AttributeReader(),
            $this->createMock(FinalizerInterface::class),
            new TemporalConfig()
        );
        $method = new \ReflectionMethod($registry, 'resolveName');
        $method->setAccessible(true);

        $this->assertSame($expectedName, $method->invoke($registry, $class));
    }

    public function resolveNameDataProvider(): \Traversable
    {
        yield [new \ReflectionClass(SomeActivity::class), 'worker1'];
        yield [new \ReflectionClass(SomeWorkflow::class), 'worker2'];
        yield [new \ReflectionClass(WithoutAttribute::class), WorkerFactoryInterface::DEFAULT_TASK_QUEUE];
    }
}
