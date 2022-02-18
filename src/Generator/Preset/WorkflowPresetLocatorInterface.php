<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator\Preset;

interface WorkflowPresetLocatorInterface
{
    /**
     * Get list of available presets
     * @return array<non-empty-string, PresetInterface>
     */
    public function getPresets(): array;
}
