<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

use Spiral\Core\InjectableConfig;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

final class TemporalConfig extends InjectableConfig
{
    public const CONFIG = 'temporal';

    protected array $config = [
        'address' => 'localhost:7233',
        'namespace' => 'App\\Workflow',
        'temporalNamespace' => 'default',
        'defaultWorker' => WorkerFactoryInterface::DEFAULT_TASK_QUEUE,
        'workers' => []
    ];

    public function getDefaultNamespace(): string
    {
        return $this->config['namespace'];
    }

    public function getTemporalNamespace(): string
    {
        return $this->config['temporalNamespace'];
    }

    public function getAddress(): string
    {
        return $this->config['address'];
    }

    public function getDefaultWorker(): string
    {
        return $this->config['defaultWorker'];
    }

    /** @psalm-return array<non-empty-string, WorkerOptions> */
    public function getWorkers(): array
    {
        return (array) $this->config['workers'];
    }
}
