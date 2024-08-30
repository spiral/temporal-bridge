<?php

declare(strict_types=1);

use Spiral\TemporalBridge\Config\ClientConfig;
use Spiral\TemporalBridge\Config\ConnectionConfig;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\Context;

return [
    'client' => env('TEMPORAL_CONNECTION', 'default'),
    'clients' => [
        'default' => ClientConfig::new(ConnectionConfig::new('localhost:7233')),
        'ssl' => ClientConfig::new(
            ConnectionConfig::new(address: 'ssl:7233')
                ->withTls(
                    rootCerts: '/path/to/crt',
                    privateKey: '/path/to/clientKey',
                    certChain: '/path/to/clientPem',
                ),
            options: (new ClientOptions())
                ->withNamespace('foo-bar'),
            context: Context::default()->withMetadata(['foo' => ['bar']]),
        ),
    ],
];
