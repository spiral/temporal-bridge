<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Config;

use Spiral\TemporalBridge\Config\ConnectionConfig;
use Spiral\TemporalBridge\Tests\TestCase;

final class ConnectionConfigTest extends TestCase
{
    public function testCreateSecure(): void
    {
        $config = ConnectionConfig::createSecure(
            address: 'localhost:2222',
            rootCerts: 'crt',
            privateKey: 'clientKey',
            certChain: 'clientPem',
        );

        $this->assertTrue($config->secure);
        $this->assertSame('localhost:2222', $config->address);
        $this->assertSame('crt', $config->rootCerts);
        $this->assertSame('clientKey', $config->privateKey);
        $this->assertSame('clientPem', $config->certChain);
    }

    public function testCreateInsecure(): void
    {
        $config = ConnectionConfig::createInsecure(
            address: 'localhost:1111',
        );

        $this->assertFalse($config->secure);
        $this->assertSame('localhost:1111', $config->address);
    }

    public function testCreateCloud(): void
    {
        $config = ConnectionConfig::createCloud(
            address: 'localhost:1111',
            privateKey: 'clientKey',
            certChain: 'clientPem',
        );

        $this->assertTrue($config->secure);
        $this->assertSame('localhost:1111', $config->address);
        $this->assertSame('clientKey', $config->privateKey);
        $this->assertSame('clientPem', $config->certChain);
    }

    public function testWithAuthKey(): void
    {
        $config = ConnectionConfig::createSecure(
            address: 'localhost:1111',
            certChain: 'clientPem',
        );

        $newConfig = $config->withAuthKey($key = 'authKey');

        $this->assertNotSame($config, $newConfig);
        $this->assertNull($config->authToken);
        $this->assertSame($key, $newConfig->authToken);
    }

    public function testWithAuthKeyNull(): void
    {
        $config = ConnectionConfig::createSecure(
            address: 'localhost:1111',
        )->withAuthKey('authKey');

        $newConfig = $config->withAuthKey(null);

        $this->assertNotSame($config, $newConfig);
        $this->assertNotNull($config->authToken);
        $this->assertNull($newConfig->authToken);
    }

    public function testWithAuthKeyStringable(): void
    {
        $config = ConnectionConfig::createSecure(
            address: 'localhost:1111',
        )->withAuthKey(
            $key = new class() implements \Stringable {
                public function __toString(): string
                {
                    return 'authKey';
                }
            }
        );

        $this->assertSame($key, $config->authToken);
    }
}
