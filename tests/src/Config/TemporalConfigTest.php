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
use Testo\Assert;
use Testo\Test;

#[Test]
final class TemporalConfigTest extends TestCase
{
    public function testGetsDefaultTemporalNamespaceIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        Assert::same($config->getTemporalNamespace(), 'default');
    }

    public function testGetsDefaultTemporalNamespace(): void
    {
        $config = new TemporalConfig([
            'temporalNamespace' => 'foo',
        ]);

        Assert::same($config->getTemporalNamespace(), 'foo');
    }

    public function testGetConnectionFromAddress(): void
    {
        $config = self::createLegacyConfig([
            'address' => 'localhost:1111',
        ]);

        $client = $config->getClientConfig('default');
        Assert::same($client::class, ClientConfig::class);

        Assert::same($client->connection->address, 'localhost:1111');
    }

    public function testLegacyClientOptionsKeepNamespace(): void
    {
        $config = self::createLegacyConfig([
            'address' => 'localhost:1111',
            'clientOptions' => (new ClientOptions())->withNamespace('foo'),
        ]);

        Assert::same($config->getClientOptions()->namespace, 'foo');
        Assert::same($config->getTemporalNamespace(), 'foo');
    }

    public function testLegacyTemporalNamespaceOverridesClientOptionsNamespace(): void
    {
        $config = self::createLegacyConfig([
            'address' => 'localhost:1111',
            'clientOptions' => (new ClientOptions())->withNamespace('foo'),
            'temporalNamespace' => 'bar',
        ]);

        Assert::same($config->getClientOptions()->namespace, 'bar');
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

        Assert::true($connection->isSecure());
        Assert::same($connection->address, 'localhost:2222');
        Assert::same($connection->tls->rootCerts, 'crt');
        Assert::same($connection->tls->privateKey, 'clientKey');
        Assert::same($connection->tls->certChain, 'clientPem');
        Assert::same($connection->tls->serverName, 'localhost');
    }

    public function testGetsDefaultWorker(): void
    {
        $config = new TemporalConfig([
            'defaultWorker' => 'some-worker',
        ]);

        Assert::same($config->getDefaultWorker(), 'some-worker');
    }

    public function testGetsDefaultWorkerIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        Assert::same($config->getDefaultWorker(), WorkerFactoryInterface::DEFAULT_TASK_QUEUE);
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

        Assert::same($config->getWorkers(), $workers);
    }

    public function testGetsWorkersIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        Assert::same($config->getWorkers(), []);
    }

    public function testGetsUndefinedClientOptions(): void
    {
        $config = new TemporalConfig([
            'temporalNamespace' => 'foo',
        ]);

        $options = $config->getClientOptions();

        Assert::same($options->namespace, 'foo');
    }

    public function testGetsClientOptions(): void
    {
        $config = new TemporalConfig([
            'clientOptions' => $options = new ClientOptions(),
        ]);

        Assert::same($config->getClientOptions(), $options);
    }

    public function testGetsAddressOfDefaultClient(): void
    {
        $config = new TemporalConfig([
            'client' => 'secondary',
            'clients' => [
                'default' => new ClientConfig(new ConnectionConfig('localhost:7233')),
                'secondary' => new ClientConfig(new ConnectionConfig('secondary:7233')),
            ],
        ]);

        Assert::same($config->getAddress(), 'secondary:7233');
    }

    /**
     * Builds a config from the legacy `address` options and asserts the deprecation they raise.
     */
    private static function createLegacyConfig(array $options): TemporalConfig
    {
        $deprecations = [];
        // A deprecation left to reach STDERR makes Infection abort its initial test run.
        \set_error_handler(static function (int $errno, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;
            return true;
        }, \E_USER_DEPRECATED);

        try {
            $config = new TemporalConfig($options);
        } finally {
            \restore_error_handler();
        }

        Assert::same($deprecations, ['Temporal options `address`, `clientOptions`, `temporalNamespace` are deprecated.']);

        return $config;
    }
}
