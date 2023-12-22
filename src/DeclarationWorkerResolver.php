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
     * Find the worker name for the given workflow or class declaration. If no worker is assigned, the default worker
     * name is returned.
     */
    public function resolve(\ReflectionClass $declaration): string
    {
        return $this->resolveQueueName($declaration) ?? $this->config->getDefaultWorker();
    }

    private function resolveQueueName(\ReflectionClass $declaration): ?string
    {
        $assignWorker = $this->reader->firstClassMetadata($declaration, AssignWorker::class);

        if ($assignWorker !== null) {
            return $assignWorker->name;
        }

        $parents = $declaration->getInterfaceNames();
        foreach ($parents as $parent) {
            $queueName = $this->resolveQueueName(new \ReflectionClass($parent));
            if ($queueName !== null) {
                return $queueName;
            }
        }

        return null;
    }
}
