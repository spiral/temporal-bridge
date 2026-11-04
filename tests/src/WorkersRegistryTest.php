<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Boot\FinalizerInterface;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Exception\WorkersRegistryException;
use Spiral\TemporalBridge\WorkerFactoryInterface as BridgeWorkerFactoryInterface;
use Spiral\TemporalBridge\WorkersRegistry;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Worker\Transport\RPCConnectionInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class WorkersRegistryTest extends TestCase
{
    public function testRegisterWorker(): void
    {
        $registry = new WorkersRegistry(
            $factory = \Mockery::mock(WorkerFactoryInterface::class)->shouldIgnoreMissing(),
            \Mockery::mock(FinalizerInterface::class)->shouldIgnoreMissing(),
            new TemporalConfig(['workers' => []]),
        );
        $factory->shouldReceive('newWorker')->times(1)->with('foo', null, \Mockery::andAnyOtherArgs())->andReturn($worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $worker->shouldReceive('registerActivityFinalizer')->times(1);

        $registry->register('foo', null);
    }

    public function testRegisterWorkerViaBridgeFactory(): void
    {
        $factory = \Mockery::mock(BridgeWorkerFactoryInterface::class);
        $factory->shouldReceive('create')->once()->with('foo')->andReturn($worker = \Mockery::mock(WorkerInterface::class));

        $registry = new WorkersRegistry(
            $factory,
            \Mockery::mock(FinalizerInterface::class),
            new TemporalConfig(['workers' => []]),
        );

        $registry->register('foo', WorkerOptions::new());

        Assert::true($registry->has('foo'));
        Assert::same($registry->get('foo'), $worker);
    }

    public function testRegisterWorkerWithExistsName(): void
    {
        Expect::exception(WorkersRegistryException::class)->withMessageContaining('Temporal worker with given name `foo` has already been registered.');

        $registry = new WorkersRegistry(
            \Mockery::mock(WorkerFactoryInterface::class, [
                'newWorker' => \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(),
            ]),
            \Mockery::mock(FinalizerInterface::class)->shouldIgnoreMissing(),
            new TemporalConfig(['workers' => []]),
        );

        $registry->register('foo', null);
        $registry->register('foo', null);
    }

    public function testGetWorker(): void
    {
        $options = WorkerOptions::new();
        $options->enableSessionWorker = true;
        $factory = new WorkerFactory(
            \Mockery::mock(DataConverterInterface::class)->shouldIgnoreMissing(),
            \Mockery::mock(RPCConnectionInterface::class)->shouldIgnoreMissing(),
        );

        $registry = new WorkersRegistry(
            $factory,
            \Mockery::mock(FinalizerInterface::class)->shouldIgnoreMissing(),
            new TemporalConfig(['workers' => ['worker1' => $options]]),
        );

        $worker = $registry->get('worker1');
        Assert::instanceOf($worker, WorkerInterface::class);
        Assert::true($worker->getOptions()->enableSessionWorker);

        Assert::same($registry->get('worker1'), $worker);

        $worker = $registry->get('worker2');
        Assert::instanceOf($worker, WorkerInterface::class);
        Assert::false($worker->getOptions()->enableSessionWorker);
    }

    public function testHasWorker(): void
    {
        $registry = new WorkersRegistry(
            \Mockery::mock(WorkerFactoryInterface::class, [
                'newWorker' => \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(),
            ]),
            \Mockery::mock(FinalizerInterface::class)->shouldIgnoreMissing(),
            new TemporalConfig(),
        );

        $registry->get('foo');
        Assert::false($registry->has('default'));

        $registry->get('default');
        Assert::true($registry->has('default'));
    }
}
