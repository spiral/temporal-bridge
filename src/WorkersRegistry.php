<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Boot\FinalizerInterface;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Exception\WorkersRegistryException;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactory;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;

final class WorkersRegistry implements WorkersRegistryInterface
{
    /** @psalm-var array<non-empty-string, WorkerInterface> */
    private array $workers = [];

    /** @psalm-param array<non-empty-string, WorkerOptions> $options */
    public function __construct(
        private readonly WorkerFactoryInterface|TemporalWorkerFactory $workerFactory,
        private readonly FinalizerInterface $finalizer,
        private readonly TemporalConfig $config,
    ) {
    }

    public function register(string $name, ?WorkerOptions $options): void
    {
        \assert($name !== '');

        if ($this->has($name)) {
            throw new WorkersRegistryException(
                \sprintf('Temporal worker with given name `%s` has already been registered.', $name)
            );
        }

        if ($this->workerFactory instanceof WorkerFactoryInterface) {
            $this->workers[$name] = $this->workerFactory->create($name);
        } else {
            $this->workers[$name] = $this->workerFactory->newWorker($name, $options);
            $this->workers[$name]->registerActivityFinalizer(fn() => $this->finalizer->finalize());
        }
    }

    public function get(string $name): WorkerInterface
    {
        \assert($name !== '');

        $options = $this->config->getWorkers()[$name] ?? null;

        if (! $this->has($name)) {
            $this->register($name, $options instanceof WorkerOptions ? $options : null);
        }

        return $this->workers[$name];
    }

    public function has(string $name): bool
    {
        \assert($name !== '');

        return isset($this->workers[$name]);
    }
}
