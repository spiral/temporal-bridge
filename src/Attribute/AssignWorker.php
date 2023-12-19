<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AssignWorker
{
    public function __construct(
        public readonly string $name
    ) {
    }
}
