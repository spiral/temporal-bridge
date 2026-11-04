<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Declaration;

/**
 * @psalm-immutable
 */
final class DeclarationDto
{
    /**
     * @param non-empty-string|null $taskQueue Null takes the queues from #[AssignWorker] attributes.
     */
    public function __construct(
        public readonly DeclarationType $type,
        public readonly \ReflectionClass $class,
        public readonly ?string $taskQueue = null,
    ) {}
}
