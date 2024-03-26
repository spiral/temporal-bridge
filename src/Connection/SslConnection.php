<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

class SslConnection extends Connection
{
    /**
     * @param non-empty-string $address
     * @param non-empty-string $crt Full path to the certificate file
     * @param non-empty-string|null $clientKey Full path to the client key file
     * @param non-empty-string|null $clientPem Full path to the client pem file
     * @param string|null $overrideServerName
     */
    public function __construct(
        string $address,
        public readonly ?string $crt = null,
        public readonly ?string $clientKey = null,
        public readonly ?string $clientPem = null,
        public readonly ?string $overrideServerName = null,
    ) {
        parent::__construct($address);
    }
}
