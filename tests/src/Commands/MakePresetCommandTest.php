<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Commands;

use Spiral\TemporalBridge\Exception\PresetNotFoundException;
use Spiral\TemporalBridge\Generator\FileGeneratorInterface;
use Spiral\TemporalBridge\Generator\PhpCodePrinter;
use Spiral\TemporalBridge\Preset\PresetInterface;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\Tests\TestCase;

final class MakePresetCommandTest extends TestCase
{
    public function testNotFoundPresetShouldShowErrorMessage(): void
    {
        $this->expectException(PresetNotFoundException::class);
        $this->expectErrorMessage('Preset with given name [foo] is not defined.');

        $this->runCommand('temporal:make-preset', ['preset' => 'foo', 'name' => ' bar']);
    }

    public function testPresetWithoutGeneratorsShouldShowErrorMessage(): void
    {
        $registry = $this->mockContainer(PresetRegistryInterface::class);
        $preset = $this->mockContainer(PresetInterface::class);

        $registry->shouldReceive('findByName')->with('foo')->andReturn($preset);

        $preset->shouldReceive('init')->once();
        $preset->shouldReceive('generators')->once()->andReturn([]);

        $this->assertConsoleCommandOutputContainsStrings('temporal:make-preset', [
            'preset' => 'foo', 'name' => ' bar'
        ], [
            'Generators for preset [foo] are not found.'
        ]);
    }

    public function testPresetShouldGenerateFiles(): void
    {
        $registry = $this->mockContainer(PresetRegistryInterface::class);
        $preset = $this->mockContainer(PresetInterface::class);
        $generator = $this->mockContainer(FileGeneratorInterface::class);
        $printer = $this->mockContainer(PhpCodePrinter::class);

        $registry->shouldReceive('findByName')->with('foo')->andReturn($preset);

        $preset->shouldReceive('init')->once();
        $preset->shouldReceive('generators')->once()->andReturn([
            'Baz' => $generator
        ]);
        $generator->shouldReceive('generate')->andReturn($printer);
        $printer->shouldReceive('print');

        $this->assertConsoleCommandOutputContainsStrings('temporal:make-preset', [
            'preset' => 'foo', 'name' => ' bar'
        ], [
            'Generating workflow files...',
            'Class [App\Workflow\Bar\BarBaz] successfully generated.'
        ]);
    }
}
