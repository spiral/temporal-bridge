<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Config;

use Spiral\TemporalBridge\Config\ConnectionConfig;
use Spiral\TemporalBridge\Config\TlsConfig;
use Spiral\TemporalBridge\Tests\TestCase;

final class ConnectionConfigTest extends TestCase
{
    public function testCreateSecure(): void
    {
        $config = (new ConnectionConfig(address: 'localhost:2222'))
            ->withTls(
                rootCerts: 'crt',
                privateKey: 'clientKey',
                certChain: 'clientPem',
            );

        $this->assertTrue($config->isSecure());
        $this->assertSame('localhost:2222', $config->address);
        $this->assertSame('crt', $config->tls->rootCerts);
        $this->assertSame('clientKey', $config->tls->privateKey);
        $this->assertSame('clientPem', $config->tls->certChain);
    }

    public function testCreateInsecure(): void
    {
        $config = new ConnectionConfig(
            address: 'localhost:1111',
        );

        $this->assertFalse($config->isSecure());
        $this->assertSame('localhost:1111', $config->address);
    }

    public function testWithAuthKey(): void
    {
        $config = new ConnectionConfig(
            address: 'localhost:1111',
            tls: new TlsConfig(
                certChain: 'clientPem',
            )
        );

        $newConfig = $config->withAuthKey($key = 'authKey');

        $this->assertNotSame($config, $newConfig);
        $this->assertNull($config->authToken);
        $this->assertSame($key, $newConfig->authToken);
    }

    public function testWithAuthKeyNull(): void
    {
        $config = (new ConnectionConfig(address: 'localhost:1111'))
            ->withTls()
            ->withAuthKey('authKey');

        $newConfig = $config->withAuthKey(null);

        $this->assertNotSame($config, $newConfig);
        $this->assertNotNull($config->authToken);
        $this->assertNull($newConfig->authToken);
        $this->assertTrue($config->isSecure());
        $this->assertTrue($newConfig->isSecure());
    }

    public function testWithAuthKeyStringable(): void
    {
        $config = (new ConnectionConfig(address: 'localhost:1111'))
            ->withTls()
            ->withAuthKey(
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
