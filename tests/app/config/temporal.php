<?php

declare(strict_types=1);

use Spiral\TemporalBridge\Config\ConnectionConfig;

return [
    'connection' => env('TEMPORAL_CONNECTION', 'default'),
    'connections' => [
        'default' => ConnectionConfig::createInsecure(
            address: 'localhost:7233',
        ),
        'ssl' => ConnectionConfig::createSecure(
            address: 'ssl:7233',
            rootCerts: '/path/to/crt',
            privateKey: '/path/to/clientKey',
            certChain: '/path/to/clientPem',
        ),
        'temporal_cloud' => ConnectionConfig::createCloud(
            address: 'ssl:7233',
            privateKey: '/path/to/clientKey',
            certChain: '/path/to/clientPem',
        ),
    ],
];
