<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Preset;

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

        $registry = new WorkersRegistry(
            new WorkerFactory(
                $this->createMock(DataConverterInterface::class),
                $this->createMock(RPCConnectionInterface::class)
            ), [
            'withOptions' => $options
        ]);

        $worker = $registry->get('withOptions');
        $this->assertInstanceOf(WorkerInterface::class, $worker);
        $this->assertTrue($worker->getOptions()->enableSessionWorker);

        $worker = $registry->get(WorkerFactoryInterface::DEFAULT_TASK_QUEUE);
        $this->assertInstanceOf(WorkerInterface::class, $worker);
        $this->assertFalse($worker->getOptions()->enableSessionWorker);
    }

    public function testHasWorker(): void
    {
        $registry = new WorkersRegistry($this->createMock(WorkerFactoryInterface::class));
        $method = new \ReflectionMethod($registry, 'hasWorker');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($registry, 'test'));

        $registry->get('test');
        $this->assertTrue($method->invoke($registry, 'test'));
    }
}
