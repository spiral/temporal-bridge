<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

interface WorkflowPresetLocatorInterface
{
    /**
     * Get list of available presets
     * @return array<non-empty-string, PresetInterface>
     */
    public function getPresets(): array;
}
