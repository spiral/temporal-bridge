<?php

declare(strict_types=1);

use Spiral\TemporalBridge\Connection\DsnConnection;
use Spiral\TemporalBridge\Connection\SslConnection;

return [
    'connection' => 'default',
    'connections' => [
        'default' => new DsnConnection(
            address: 'localhost:7233',
        ),
        'ssl' => new SslConnection(
            address: 'ssl:7233',
            crt: '/path/to/crt',
            clientKey: '/path/to/clientKey',
            clientPem: '/path/to/clientPem',
            overrideServerName: 'overrideServerName',
        ),
    ],
];
