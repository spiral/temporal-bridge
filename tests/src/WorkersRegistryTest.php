<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Boot\FinalizerInterface;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Exception\WorkersRegistryException;
use Spiral\TemporalBridge\WorkersRegistry;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Worker\Transport\RPCConnectionInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory;

final class WorkersRegistryTest extends TestCase
{
    public function testRegisterWorker(): void
    {
        $registry = new WorkersRegistry(
            $factory = $this->createMock(WorkerFactoryInterface::class),
            $this->createMock(FinalizerInterface::class),
            new TemporalConfig(['workers' => []])
        );
        $factory
            ->expects($this->exactly(1))
            ->method('newWorker')
            ->with('foo', null)
            ->willReturn($worker = $this->createMock(WorkerInterface::class));

        $worker
            ->expects($this->exactly(1))
            ->method('registerActivityFinalizer');

        $registry->register('foo', null);
    }

    public function testRegisterWorkerWithExistsName(): void
    {
        $this->expectException(WorkersRegistryException::class);
        $this->expectErrorMessage('Temporal worker with given name `foo` has already been registered.');

        $registry = new WorkersRegistry(
            $this->createMock(WorkerFactoryInterface::class),
            $this->createMock(FinalizerInterface::class),
            new TemporalConfig(['workers' => []])
        );

        $registry->register('foo', null);
        $registry->register('foo', null);
    }

    public function testGetWorker(): void
    {
        $options = WorkerOptions::new();
        $options->enableSessionWorker = true;
        $factory = new WorkerFactory(
            $this->createMock(DataConverterInterface::class),
            $this->createMock(RPCConnectionInterface::class)
        );

        $registry = new WorkersRegistry(
            $factory,
            $this->createMock(FinalizerInterface::class),
            new TemporalConfig(['workers' => ['worker1' => $options]])
        );

        $worker = $registry->get('worker1');
        $this->assertInstanceOf(WorkerInterface::class, $worker);
        $this->assertTrue($worker->getOptions()->enableSessionWorker);

        $this->assertSame($worker, $registry->get('worker1'));

        $worker = $registry->get('worker2');
        $this->assertInstanceOf(WorkerInterface::class, $worker);
        $this->assertFalse($worker->getOptions()->enableSessionWorker);
    }

    public function testHasWorker(): void
    {
        $registry = new WorkersRegistry(
            $this->createMock(WorkerFactoryInterface::class),
            $this->createMock(FinalizerInterface::class),
            new TemporalConfig()
        );

        $registry->get('foo');
        $this->assertFalse($registry->has('default'));

        $registry->get('default');
        $this->assertTrue($registry->has('default'));
    }
}
