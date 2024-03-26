<?php

declare(strict_types=1);

use Spiral\TemporalBridge\Connection\Connection;
use Spiral\TemporalBridge\Connection\SslConnection;
use Spiral\TemporalBridge\Connection\TemporalCloudConnection;

return [
    'connection' => env('TEMPORAL_CONNECTION', 'default'),
    'connections' => [
        'default' => new Connection(
            address: 'localhost:7233',
        ),
        'ssl' => new SslConnection(
            address: 'ssl:7233',
            crt: '/path/to/crt',
            clientKey: '/path/to/clientKey',
            clientPem: '/path/to/clientPem',
            overrideServerName: 'overrideServerName',
        ),
        'temporal_cloud' => new TemporalCloudConnection(
            address: 'ssl:7233',
            clientKey: '/path/to/clientKey',
            clientPem: '/path/to/clientPem',
        ),
    ],
];
