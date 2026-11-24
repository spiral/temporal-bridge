<?php

declare(strict_types=1);

use Spiral\TemporalBridge\Config\ClientConfig;
use Spiral\TemporalBridge\Config\ConnectionConfig;

return [
    'client' => 'default',
    'clients' => [
        'default' => new ClientConfig(new ConnectionConfig('127.0.0.1:7233')),
    ],
    'defaultWorker' => 'main-queue',
];
