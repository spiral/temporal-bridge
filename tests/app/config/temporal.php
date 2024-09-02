<?php

declare(strict_types=1);

use Spiral\TemporalBridge\Config\ClientConfig;
use Spiral\TemporalBridge\Config\ConnectionConfig;
use Spiral\TemporalBridge\Config\TlsConfig;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\Context;

return [
    'client' => env('TEMPORAL_CONNECTION', 'default'),
    'clients' => [
        'default' => new ClientConfig(new ConnectionConfig('localhost:7233')),
        'ssl' => new ClientConfig(
            new ConnectionConfig(
                address: 'ssl:7233',
                tls: new TlsConfig(
                    rootCerts: '/path/to/crt',
                    privateKey: '/path/to/clientKey',
                    certChain: '/path/to/clientPem',
                ),
            ),
            options: (new ClientOptions())
                ->withNamespace('foo-bar'),
            context: Context::default()->withMetadata(['foo' => ['bar']]),
        ),
    ],
];
