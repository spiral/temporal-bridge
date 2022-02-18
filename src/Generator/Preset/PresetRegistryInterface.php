<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator\Preset;

use Spiral\TemporalBridge\Exception\PresetNotFoundException;

interface PresetRegistryInterface
{
    /**
     * Register a new preset
     */
    public function register(string $name, PresetInterface $preset): void;

    /**
     * List of available presets
     * @return array<non-empty-string, PresetInterface>
     */
    public function getList(): array;

    /**
     * Find an exists preset by name
     * @throws PresetNotFoundException
     */
    public function findByName(string $name): PresetInterface;
}
