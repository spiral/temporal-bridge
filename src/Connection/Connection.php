<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

class Connection
{
    /**
     * @param non-empty-string $address
     */
    public function __construct(
        public readonly string $address,
    ) {
    }
}
