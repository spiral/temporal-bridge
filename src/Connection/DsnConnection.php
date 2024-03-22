<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

class DsnConnection extends Connection
{
    public function __construct(
        public readonly string $address,
    ) {
        [$host, $port] = \explode(':', $address);
        parent::__construct($host, (int)$port);
    }
}
