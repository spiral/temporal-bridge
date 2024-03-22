<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

class Connection
{
    /**
     * @param non-empty-string $host
     * @param int $port
     */
    public function __construct(
        public readonly string $host,
        public readonly int $port,
    ) {
    }

    public function getAddress(): string
    {
        return $this->host . ':' . $this->port;
    }
}
