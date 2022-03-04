<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Commands;

use Spiral\TemporalBridge\Preset\PresetInterface;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\Tests\TestCase;

final class PresetListCommandTest extends TestCase
{
    public function testErrorMessageShouldBeShownIfPresetsNotFound()
    {
        $this->assertConsoleCommandOutputContainsStrings('temporal:presets', [], [
            'No available Workflow presets found.'
        ]);
    }

    public function testPresetsShouldBeFound()
    {
        $registry = $this->mockContainer(PresetRegistryInterface::class);
        $preset = $this->mockContainer(PresetInterface::class);

        $registry->shouldReceive('getList')->once()->andReturn([
            'foo' => $preset
        ]);

        $preset->shouldReceive('getDescription')->once()->andReturn('First preset description');

        $this->assertConsoleCommandOutputContainsStrings('temporal:presets', [], [
            '| name | description              |',
            '| foo  | First preset description |'
        ]);
    }
}
