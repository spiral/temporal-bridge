<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Config;

use Spiral\TemporalBridge\Config\ClientConfig;
use Spiral\TemporalBridge\Config\ConnectionConfig;
use Spiral\TemporalBridge\Config\TemporalConfig;
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

        $client = $config->getClientConfig('default');
        $this->assertSame(ClientConfig::class, $client::class);

        $this->assertSame('localhost:1111', $client->connection->address);
    }

    public function testGetTlsConnection(): void
    {
        $config = new TemporalConfig([
            'clients' => [
                'default' => new ClientConfig(
                    (new ConnectionConfig(address: 'localhost:2222'))
                        ->withTls(
                            rootCerts: 'crt',
                            privateKey: 'clientKey',
                            certChain: 'clientPem',
                            serverName: 'localhost',
                        ),
                ),
            ],
        ]);

        $client = $config->getClientConfig('default');
        $connection = $client->connection;

        $this->assertTrue($connection->isSecure());
        $this->assertSame('localhost:2222', $connection->address);
        $this->assertSame('crt', $connection->tls->rootCerts);
        $this->assertSame('clientKey', $connection->tls->privateKey);
        $this->assertSame('clientPem', $connection->tls->certChain);
        $this->assertSame('localhost', $connection->tls->serverName);
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
