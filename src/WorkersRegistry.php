<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Boot\FinalizerInterface;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Exception\WorkersRegistryException;
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
        private FinalizerInterface $finalizer,
        private TemporalConfig $config
    ) {
    }

    public function register(string $name, ?WorkerOptions $options): void
    {
        if ($this->has($name)) {
            throw new WorkersRegistryException(
                \sprintf('Temporal worker with given name `%s` has already been registered.', $name)
            );
        }

        $this->workers[$name] = $this->workerFactory->newWorker($name, $options);
        $this->workers[$name]->registerActivityFinalizer(fn() => $this->finalizer->finalize());
    }

    public function get(string $name): WorkerInterface
    {
        $options = $this->config->getWorkers();

        if (! $this->has($name)) {
            $this->register($name, $options[$name] ?? null);
        }

        return $this->workers[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->workers[$name]);
    }
}
