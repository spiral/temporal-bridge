<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Config;

use Spiral\TemporalBridge\Config\ConnectionConfig;
use Spiral\TemporalBridge\Config\TlsConfig;
use Spiral\TemporalBridge\Tests\TestCase;
use Testo\Assert;
use Testo\Test;

#[Test]
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

        Assert::true($config->isSecure());
        Assert::same($config->address, 'localhost:2222');
        Assert::same($config->tls->rootCerts, 'crt');
        Assert::same($config->tls->privateKey, 'clientKey');
        Assert::same($config->tls->certChain, 'clientPem');
    }

    public function testCreateInsecure(): void
    {
        $config = new ConnectionConfig(
            address: 'localhost:1111',
        );

        Assert::false($config->isSecure());
        Assert::same($config->address, 'localhost:1111');
    }

    public function testWithAuthKey(): void
    {
        $config = new ConnectionConfig(
            address: 'localhost:1111',
            tls: new TlsConfig(
                certChain: 'clientPem',
            ),
        );

        $newConfig = $config->withAuthKey($key = 'authKey');

        Assert::notSame($newConfig, $config);
        Assert::null($config->authToken);
        Assert::same($newConfig->authToken, $key);
    }

    public function testWithAuthKeyNull(): void
    {
        $config = (new ConnectionConfig(address: 'localhost:1111'))
            ->withTls()
            ->withAuthKey('authKey');

        $newConfig = $config->withAuthKey(null);

        Assert::notSame($newConfig, $config);
        Assert::notNull($config->authToken);
        Assert::null($newConfig->authToken);
        Assert::true($config->isSecure());
        Assert::true($newConfig->isSecure());
    }

    public function testWithAuthKeyStringable(): void
    {
        $config = (new ConnectionConfig(address: 'localhost:1111'))
            ->withTls()
            ->withAuthKey(
                $key = new class implements \Stringable {
                    public function __toString(): string
                    {
                        return 'authKey';
                    }
                },
            );

        Assert::same($config->authToken, $key);
    }
}
