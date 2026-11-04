<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @psalm-immutable
 * @psalm-suppress DeprecatedClass
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE), NamedArgumentConstructor]
final class AssignWorker
{
    /**
     * @param non-empty-string $taskQueue Task queue name.
     */
    public function __construct(
        public readonly string $taskQueue,
    ) {}
}
