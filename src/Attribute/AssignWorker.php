<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class AssignWorker
{
    public function __construct(
        public readonly string $name
    ) {
    }
}
