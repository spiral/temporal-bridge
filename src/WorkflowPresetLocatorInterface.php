<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\TemporalBridge\Preset\PresetInterface;

interface WorkflowPresetLocatorInterface
{
    /**
     * Get list of available presets
     * @return array<string, PresetInterface>
     */
    public function getPresets(): array;
}
