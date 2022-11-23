<?php

namespace Spiral\TemporalBridge\Tests;

class TestCase extends \Spiral\Testing\TestCase
{

    public function rootDirectory(): string
    {
        return __DIR__.'/../';
    }

    public function defineBootloaders(): array
    {
        return [
            \Spiral\Boot\Bootloader\ConfigurationBootloader::class,
            \Spiral\Bootloader\Attributes\AttributesBootloader::class,
            \Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader::class,
            \Spiral\TemporalBridge\Bootloader\PrototypeBootloader::class,
            // ...
        ];
    }
}
