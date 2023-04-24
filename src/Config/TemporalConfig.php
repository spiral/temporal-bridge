<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Temporal\Exception\ExceptionInterceptorInterface;
use Temporal\Internal\Interceptor\Interceptor;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

/**
 * @psalm-type TInterceptor = Interceptor|class-string<Interceptor>|Autowire<Interceptor>
 * @psalm-type TExceptionInterceptor = ExceptionInterceptorInterface|class-string<ExceptionInterceptorInterface>|Autowire<ExceptionInterceptorInterface>
 * @psalm-type TWorker = array{
 *     options?: WorkerOptions,
 *     interceptors?: TInterceptor[],
 *     exception_interceptor?: TExceptionInterceptor
 * }
 *
 * @property array{
 *     address: non-empty-string,
 *     namespace: non-empty-string,
 *     temporalNamespace: non-empty-string,
 *     defaultWorker: non-empty-string,
 *     workers: array<non-empty-string, WorkerOptions|TWorker>
 * } $config
 */
final class TemporalConfig extends InjectableConfig
{
    public const CONFIG = 'temporal';

    protected array $config = [
        'address' => 'localhost:7233',
        'namespace' => 'App\\Workflow',
        'temporalNamespace' => 'default',
        'defaultWorker' => WorkerFactoryInterface::DEFAULT_TASK_QUEUE,
        'workers' => [],
    ];

    /**
     * @return non-empty-string
     */
    public function getDefaultNamespace(): string
    {
        return $this->config['namespace'];
    }

    /**
     * @return non-empty-string
     */
    public function getTemporalNamespace(): string
    {
        return $this->config['temporalNamespace'];
    }

    /**
     * @return non-empty-string
     */
    public function getAddress(): string
    {
        return $this->config['address'];
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
}
