<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

class Connection
{
    /**
     * @param non-empty-string $address
     *
     * ```php
     * new Connection('localhost:7233');
     * ```
     */
    public function __construct(
        public readonly string $address,
    ) {
    }
}
