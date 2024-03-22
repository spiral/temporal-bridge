<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\TemporalBridge\Connection\Connection;
use Spiral\TemporalBridge\Connection\DsnConnection;
use Spiral\TemporalBridge\Connection\SslConnection;
use Temporal\Client\ClientOptions;
use Temporal\Exception\ExceptionInterceptorInterface;
use Temporal\Internal\Interceptor\Interceptor;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

/**
 * @psalm-type TInterceptor = Interceptor|class-string<Interceptor>|Autowire<Interceptor>
 * @psalm-type TExceptionInterceptor = ExceptionInterceptorInterface|class-string<ExceptionInterceptorInterface>|Autowire<ExceptionInterceptorInterface>
 * @psalm-type TWorker = array{
 *     options?: WorkerOptions,
 *     exception_interceptor?: TExceptionInterceptor
 * }
 *
 * @property array{
 *     address?: non-empty-string|null,
 *     connection: non-empty-string,
 *     connections: array<non-empty-string, Connection>,
 *     temporalNamespace: non-empty-string,
 *     defaultWorker: non-empty-string,
 *     workers: array<non-empty-string, WorkerOptions|TWorker>,
 *     interceptors?: TInterceptor[],
 *     clientOptions?: ClientOptions
 * } $config
 */
final class TemporalConfig extends InjectableConfig
{
    public const CONFIG = 'temporal';

    protected array $config = [
        'connection' => 'default',
        'connections' => [],
        'temporalNamespace' => 'default',
        'defaultWorker' => WorkerFactoryInterface::DEFAULT_TASK_QUEUE,
        'workers' => [],
        'interceptors' => [],
        'clientOptions' => null,
    ];

    /**
     * @return non-empty-string
     */
    public function getTemporalNamespace(): string
    {
        return $this->config['temporalNamespace'];
    }

    public function getDefaultConnection(): string
    {
        return $this->config['connection'] ?? 'default';
    }

    public function getConnection(string $name): Connection
    {
        // Legacy support. Will be removed in further versions.
        // If you read this, please remove address from your configuration and use connections instead.
        $address = $this->config['address'] ?? null;
        if ($address !== null) {
            \trigger_error(
                'Using `address` is deprecated, use `connections` instead.',
                \E_USER_DEPRECATED,
            );
            return new Connection(address: $address);
        }

        if (isset($this->config['connections'][$name])) {
            return $this->config['connections'][$name];
        }

        throw new \InvalidArgumentException(\sprintf('Connection `%s` is not defined.', $name));
    }

    /**
     * @deprecated
     */
    public function getAddress(): string
    {
        return $this->getConnection($this->getDefaultConnection())->address;
    }

    /**
     * @return non-empty-string
     */
    public function getDefaultWorker(): string
    {
        return $this->config['defaultWorker'];
    }

    /**
     * @return array<non-empty-string, WorkerOptions|TWorker>
     */
    public function getWorkers(): array
    {
        return $this->config['workers'] ?? [];
    }

    /**
     * @return TInterceptor[]
     */
    public function getInterceptors(): array
    {
        return $this->config['interceptors'] ?? [];
    }

    public function getClientOptions(): ClientOptions
    {
        return $this->config['clientOptions'] ?? (new ClientOptions())->withNamespace($this->getTemporalNamespace());
    }
}
