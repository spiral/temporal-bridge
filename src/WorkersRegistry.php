<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\TemporalBridge\Attribute\RegisterWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
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
        private ReaderInterface $reader,
        private FinalizerInterface $finalizer,
        private TemporalConfig $config
    ) {
    }

    public function get(\ReflectionClass $declaration): WorkerInterface
    {
        $name = $this->resolveName($declaration);
        $options = $this->config->getWorkers();

        if (!$this->hasWorker($name)) {
            $this->workers[$name] = $this->workerFactory->newWorker($name, $options[$name] ?? null);
            $this->workers[$name]->registerActivityFinalizer(fn() => $this->finalizer->finalize());
        }

        return $this->workers[$name];
    }

    private function hasWorker(string $name): bool
    {
        return isset($this->workers[$name]);
    }

    private function resolveName(\ReflectionClass $declaration): string
    {
        $registerWorker = $this->reader->firstClassMetadata($declaration, RegisterWorker::class);

        if ($registerWorker === null) {
            return $this->config->getDefaultWorker();
        }

        return $registerWorker->name;
    }
}
