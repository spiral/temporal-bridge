<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Attributes\ReaderInterface;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;

final class DeclarationWorkerResolver
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly TemporalConfig $config,
    ) {
    }

    /**
     * Find the worker name for the given workflow or class declaration. If no worker is assigned, the default task
     * queue name is returned.
     */
    public function resolve(\ReflectionClass $declaration): array
    {
        $queue = $this->resolveTaskQueues($declaration);

        if ($queue !== []) {
            return $queue;
        }

        return [$this->config->getDefaultWorker()];
    }

    private function resolveTaskQueues(\ReflectionClass $declaration): array
    {
        $assignWorker = $this->reader->getClassMetadata($declaration, AssignWorker::class);

        $workers = [];

        foreach ($assignWorker as $worker) {
            $workers[] = $worker->taskQueue;
        }

        if ($workers !== []) {
            return $workers;
        }

        $parents = $declaration->getInterfaceNames();
        foreach ($parents as $parent) {
            $queueName = $this->resolveTaskQueues(new \ReflectionClass($parent));
            if ($queueName !== []) {
                return $queueName;
            }
        }

        return [];
    }
}
