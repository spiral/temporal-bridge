<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\Context;
use Temporal\Client\GRPC\ContextInterface;

/**
 * Temporal Client configuration.
 *
 *     ClientConfig::new(
 *         ConnectionConfig::new('localhost:7233')
 *             ->withTls(
 *                 privateKey: '/my-project.key',
 *                 certChain: '/my-project.pem',
 *             ),
 *         (new ClientOptions())
 *             ->withNamespace('default'),
 *         Context::default()
 *             ->withTimeout(4.5)
 *             ->withRetryOptions(
 *                 RpcRetryOptions::new()
 *                     ->withMaximumAttempts(5)
 *                     ->withInitialInterval(3)
 *                     ->withMaximumInterval(10)
 *                     ->withBackoffCoefficient(1.6)
 *             ),
 *         ),
 *     ),
 */
final class ClientConfig
{
    private function __construct(
        public readonly ConnectionConfig $connection,
        public readonly ClientOptions $options,
        public readonly ContextInterface $context,
    ) {}

    /**
     * Create a new client configuration.
     *
     * @param ConnectionConfig $connection
     * @param ClientOptions|null $options
     * @param ContextInterface|null $context Default Service Client context.
     */
    public static function new(
        ConnectionConfig $connection,
        ?ClientOptions $options = null,
        ?ContextInterface $context = null,
    ): self {
        return new self(
            $connection,
            $options ?? new ClientOptions(),
            $context ?? Context::default(),
        );
    }
}
