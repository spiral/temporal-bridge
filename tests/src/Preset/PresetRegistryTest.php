<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Preset;

use Spiral\TemporalBridge\Exception\PresetNotFoundException;
use Spiral\TemporalBridge\Preset\PresetInterface;
use Spiral\TemporalBridge\Preset\PresetRegistry;
use Spiral\TemporalBridge\Tests\TestCase;

final class PresetRegistryTest extends TestCase
{
    public function testRegister(): void
    {
        $registry = new PresetRegistry();

        $this->assertCount(0, $registry->getList());

        $registry->register('foo', $preset = \Mockery::mock(PresetInterface::class));

        $this->assertCount(1, $registry->getList());

        $this->assertSame($preset, $registry->findByName('foo'));
    }

    public function testNotFoundPresetShouldThrowAnException(): void
    {
        $this->expectException(PresetNotFoundException::class);
        $this->expectErrorMessage('Preset with given name [foo] is not defined.');

        $registry = new PresetRegistry();

        $registry->findByName('foo');
    }
}
