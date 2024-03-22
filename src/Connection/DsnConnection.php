<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

class DsnConnection extends Connection
{
    /**
     * @param non-empty-string $address Address in format host:port
     */
    public function __construct(
        public readonly string $address,
    ) {
        [$host, $port] = \explode(':', $address);
        \assert($host !== '', 'Host must be a non-empty string');

        parent::__construct($host, (int)$port);
    }
}
