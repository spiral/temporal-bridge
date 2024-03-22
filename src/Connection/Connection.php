<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

class Connection
{
    public function __construct(
        public readonly string $address,
    ) {
    }
}
