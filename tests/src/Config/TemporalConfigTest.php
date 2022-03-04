<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Config;

use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Tests\TestCase;

final class TemporalConfigTest extends TestCase
{
    public function testGetsDefaultNamespace(): void
    {
        $config = new TemporalConfig([
            'namespace' => 'foo'
        ]);

        $this->assertSame('foo', $config->getDefaultNamespace());
    }

    public function testGetsDefaultNamespaceIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame('App\\Workflow', $config->getDefaultNamespace());
    }

    public function testGetsAddress(): void
    {
        $config = new TemporalConfig([
            'address' => 'localhost:1111'
        ]);

        $this->assertSame('localhost:1111', $config->getAddress());
    }

    public function testGetsAddressIfItNotSet(): void
    {
        $config = new TemporalConfig([]);

        $this->assertSame('localhost:7233', $config->getAddress());
    }

}
