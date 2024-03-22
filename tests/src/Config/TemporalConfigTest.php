<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Config;

use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Connection\Connection;
use Spiral\TemporalBridge\Connection\DsnConnection;
use Spiral\TemporalBridge\Connection\SslConnection;
use Spiral\TemporalBridge\Tests\TestCase;
use Temporal\Client\ClientOptions;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

final class TemporalConfigTest extends TestCase
{
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

    public function testGetConnectionFromAddress(): void
    {
        $config = new TemporalConfig([
            'address' => 'localhost:1111',
        ]);

        $connection = $config->getConnection('default');
        $this->assertSame(Connection::class, $connection::class);

        $this->assertSame('localhost:1111', $connection->address);
    }

    public function testGetSslConnection(): void
    {
        $config = new TemporalConfig([
            'connections' => [
                'default' => new SslConnection(
                    address: 'localhost:2222',
                    crt: 'crt',
                    clientKey: 'clientKey',
                    clientPem: 'clientPem',
                    overrideServerName: 'overrideServerName',
                ),
            ],
        ]);

        $connection = $config->getConnection('default');

        $this->assertSame(SslConnection::class, $connection::class);

        $this->assertSame('localhost:2222', $connection->address);
        $this->assertSame('crt', $connection->crt);
        $this->assertSame('clientKey', $connection->clientKey);
        $this->assertSame('clientPem', $connection->clientPem);
        $this->assertSame('overrideServerName', $connection->overrideServerName);
    }

    public function testGetsDefaultWorker(): void
    {
        $config = new TemporalConfig([
            'defaultWorker' => 'some-worker',
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
                    'foo',
                ],
            ],
            'withExceptionInterceptor' => [
                'exception_interceptor' => 'bar',
            ],
            'all' => [
                'options' => WorkerOptions::new(),
                'interceptors' => [
                    'foo',
                ],
                'exception_interceptor' => 'bar',
            ],
        ];

        $config = new TemporalConfig([
            'workers' => $workers,
        ]);

        $this->assertSame($workers, $config->getWorkers());
    }

    public function testGetsWorkersIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame([], $config->getWorkers());
    }

    public function testGetsUndefinedClientOptions(): void
    {
        $config = new TemporalConfig([
            'temporalNamespace' => 'foo',
        ]);

        $options = $config->getClientOptions();

        $this->assertSame('foo', $options->namespace);
    }

    public function testGetsClientOptions(): void
    {
        $config = new TemporalConfig([
            'clientOptions' => $options = new ClientOptions(),
        ]);

        $this->assertSame($options, $config->getClientOptions());
    }
}
