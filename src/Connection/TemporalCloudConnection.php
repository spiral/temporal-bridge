<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Connection;

/**
 * This connection is used to connect to Temporal Cloud.
 *
 * @see https://docs.temporal.io/cloud/get-started
 */
final class TemporalCloudConnection extends SslConnection
{
    /**
     * @param non-empty-string $address
     * @param non-empty-string $clientKey Full path to the client key file. Required.
     * @param non-empty-string $clientPem Full path to the client pem file. Required.
     */
    public function __construct(
        string $address,
        string $clientKey,
        string $clientPem,
    ) {
        parent::__construct(
            address: $address,
            clientKey: $clientKey,
            clientPem: $clientPem,
        );
    }
}
