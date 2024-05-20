<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Declaration;

final class DeclarationDto
{
    public function __construct(
        public readonly DeclarationType $type,
        public readonly \ReflectionClass $class,
        public readonly ?string $taskQueue = null,
    ) {
    }
}
