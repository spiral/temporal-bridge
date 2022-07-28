<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Config;

use Spiral\Core\InjectableConfig;

final class TemporalConfig extends InjectableConfig
{
    public const CONFIG = 'temporal';
    protected $config = [
        'address' => null,
        'namespace' => null,
        'temporalNamespace' => null,
    ];

    public function getDefaultNamespace(): string
    {
        return $this->config['namespace'] ?? 'App\\Workflow';
    }

    public function getTemporalNamespace(): string
    {
        return $this->config['temporalNamespace'] ?? 'default';
    }

    public function getAddress(): string
    {
        return $this->config['address'] ?? 'localhost:7233';
    }
}
