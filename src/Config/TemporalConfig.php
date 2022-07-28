<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

use Spiral\Core\InjectableConfig;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;

final class TemporalConfig extends InjectableConfig
{
    public const CONFIG = 'temporal';
    protected $config = [
        'address' => null,
        'namespace' => null,
        'temporalNamespace' => null,
        'defaultWorker' => WorkerFactoryInterface::DEFAULT_TASK_QUEUE,
        'workers' => [],
    ];

    public function getDefaultNamespace(): string
    {
        return $this->config['namespace'] ?? 'App\\Workflow';
    }

    public function getTemporalNamespace(): string
    {
        return $this->config['temporalNamespace'] ?? 'default';
    }

    public function getAddress(): string
    {
        return $this->config['address'] ?? 'localhost:7233';
    }

    public function getDefaultWorker(): string
    {
        return $this->config['defaultWorker'] ?? WorkerFactoryInterface::DEFAULT_TASK_QUEUE;
    }

    /** @psalm-return array<non-empty-string, WorkerOptions> */
    public function getWorkers(): array
    {
        return $this->config['workers'] ?? [];
    }
}
