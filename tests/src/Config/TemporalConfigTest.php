<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Config;

use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Tests\TestCase;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

final class TemporalConfigTest extends TestCase
{
    public function testGetsDefaultNamespace(): void
    {
        $config = new TemporalConfig([
            'namespace' => 'foo'
        ]);

        $this->assertSame('foo', $config->getDefaultNamespace());
    }

    public function testGetsDefaultNamespaceIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame('App\\Endpoint\\Temporal\\Workflow', $config->getDefaultNamespace());
    }

    public function testGetsDefaultTemporalNamespaceIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame('default', $config->getTemporalNamespace());
    }

    public function testGetsDefaultTemporalNamespace(): void
    {
        $config = new TemporalConfig([
            'temporalNamespace' => 'foo',
        ]);

        $this->assertSame('foo', $config->getTemporalNamespace());
    }

    public function testGetsAddress(): void
    {
        $config = new TemporalConfig([
            'address' => 'localhost:1111'
        ]);

        $this->assertSame('localhost:1111', $config->getAddress());
    }

    public function testGetsAddressIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame('localhost:7233', $config->getAddress());
    }

    public function testGetsDefaultWorker(): void
    {
        $config = new TemporalConfig([
            'defaultWorker' => 'some-worker'
        ]);

        $this->assertSame('some-worker', $config->getDefaultWorker());
    }

    public function testGetsDefaultWorkerIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame(WorkerFactoryInterface::DEFAULT_TASK_QUEUE, $config->getDefaultWorker());
    }

    public function testGetsWorkers(): void
    {
        $workers = [
            'first' => WorkerOptions::new(),
            'second' => WorkerOptions::new(),
            'withOptions' => [
                'options' => WorkerOptions::new(),
            ],
            'withInterceptors' => [
                'interceptors' => [
                    'foo'
                ],
            ],
            'withExceptionInterceptor' => [
                'exception_interceptor' => 'bar'
            ],
            'all' => [
                'options' => WorkerOptions::new(),
                'interceptors' => [
                    'foo'
                ],
                'exception_interceptor' => 'bar'
            ],
        ];

        $config = new TemporalConfig([
            'workers' => $workers
        ]);

        $this->assertSame($workers, $config->getWorkers());
    }

    public function testGetsWorkersIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame([], $config->getWorkers());
    }
}
