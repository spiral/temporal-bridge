<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;

final class WorkersRegistry implements WorkersRegistryInterface
{
    /** @psalm-var array<non-empty-string, WorkerInterface> */
    private array $workers = [];

    /** @psalm-param array<non-empty-string, WorkerOptions> $options */
    public function __construct(
        private WorkerFactoryInterface $workerFactory,
        private array $options = []
    ) {
    }

    public function get(string $name): WorkerInterface
    {
        if (!$this->hasWorker($name)) {
            $this->workers[$name] = $this->workerFactory->newWorker($name, $this->options[$name] ?? null);
        }

        return $this->workers[$name];
    }

    private function hasWorker(string $name): bool
    {
        return isset($this->workers[$name]);
    }
}
