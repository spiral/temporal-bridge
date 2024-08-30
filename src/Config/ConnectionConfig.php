<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

/**
 * Temporal connection and credentials configuration.
 *
 * How to connect to local Temporal server:
 *
 *     ConnectionConfig::new('localhost:7233'),
 *
 * How to connect to Temporal Cloud:
 *
 *     ConnectionConfig::new('foo-bar-default.baz.tmprl.cloud:7233')
 *         ->withTls(
 *             privateKey: '/my-project.key',
 *             certChain: '/my-project.pem',
 *         ),
 */
final class ConnectionConfig
{
    /**
     * @param non-empty-string $address
     * @param non-empty-string|\Stringable|null $authToken
     */
    private function __construct(
        public readonly string $address,
        public readonly ?TlsConfig $tlsConfig = null,
        public readonly string|\Stringable|null $authToken = null,
    ) {}

    /**
     * Check if the connection is secure.
     *
     * @psalm-assert-if-true TlsConfig $this->tlsConfig
     * @psalm-assert-if-false null $this->tlsConfig
     */
    public function isSecure(): bool
    {
        return $this->tlsConfig !== null;
    }

    /**
     * @param non-empty-string $address
     */
    public static function new(
        string $address,
    ): self {
        return new self($address);
    }

    /**
     * Set the TLS configuration for the connection.
     *
     * @param non-empty-string|null $rootCerts Root certificates string or file in PEM format.
     *         If null provided, default gRPC root certificates are used.
     * @param non-empty-string|null $privateKey Client private key string or file in PEM format.
     * @param non-empty-string|null $certChain Client certificate chain string or file in PEM format.
     * @param non-empty-string|null $serverName Server name override for TLS verification.
     */
    public function withTls(
        ?string $rootCerts = null,
        ?string $privateKey = null,
        ?string $certChain = null,
        ?string $serverName = null,
    ): self {
        return new self(
            $this->address,
            new TlsConfig($rootCerts, $privateKey, $certChain, $serverName),
        );
    }

    /**
     * Set the authentication token for the service client.
     *
     * This is the equivalent of providing an "Authorization" header with "Bearer " + the given key.
     * This will overwrite any "Authorization" header that may be on the context before each request to the
     * Temporal service.
     * You may pass your own {@see \Stringable} implementation to be able to change the key dynamically.
     *
     * @param non-empty-string|\Stringable|null $authToken
     */
    public function withAuthKey(string|\Stringable|null $authToken): self
    {
        return new self(
            $this->address,
            $this->tlsConfig,
            $authToken,
        );
    }
}
