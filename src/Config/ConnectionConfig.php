<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

/**
 * Temporal connection configuration.
 *
 * How to connect to local Temporal server:
 *
 * ```php
 * ConnectionConfig::createInsecure('localhost:7233')
 * ```
 *
 * How to connect to Temporal Cloud:
 *
 * ```php
 * ConnectionConfig::createCloud(
 *     address: 'foo-bar-default.baz.tmprl.cloud:7233',
 *     privateKey: '/my-project.key',
 *     certChain: '/my-project.pem',
 * )
 * ```
 */
final class ConnectionConfig
{
    private function __construct(
        public readonly string $address,
        public readonly bool $secure = false,
        public readonly ?string $rootCerts = null,
        public readonly ?string $privateKey = null,
        public readonly ?string $certChain = null,
    ) {}

    /**
     * @param non-empty-string $address
     */
    public static function createInsecure(
        string $address,
    ): self {
        return new self($address);
    }

    /**
     * @param non-empty-string $address
     * @param non-empty-string|null $rootCerts Root certificates string or file in PEM format.
     *         If null provided, default gRPC root certificates are used.
     * @param non-empty-string|null $privateKey Client private key string or file in PEM format.
     * @param non-empty-string|null $certChain Client certificate chain string or file in PEM format.
     */
    public static function createSecure(
        string $address,
        ?string $rootCerts = null,
        ?string $privateKey = null,
        ?string $certChain = null,
    ): self {
        return new self($address, true, $rootCerts, $privateKey, $certChain);
    }

    /**
     * Used to connect to Temporal Cloud.
     *
     * @link https://docs.temporal.io/cloud/get-started
     *
     * @param non-empty-string $address
     * @param non-empty-string $privateKey Client private key string or file in PEM format.
     * @param non-empty-string $certChain Client certificate chain string or file in PEM format.
     */
    public static function createCloud(
        string $address,
        string $privateKey,
        string $certChain,
    ): self {
        return new self($address, true, null, $privateKey, $certChain);
    }
}
