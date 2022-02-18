<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Preset;

use Spiral\TemporalBridge\Exception\PresetNotFoundException;

final class PresetRegistry implements PresetRegistryInterface
{
    /** @var PresetInterface[] */
    private array $presets = [];

    public function register(string $name, PresetInterface $preset): void
    {
        $this->presets[$name] = $preset;
    }

    public function findByName(string $name): PresetInterface
    {
        if (! isset($this->presets[$name])) {
            throw new PresetNotFoundException(
                \sprintf(
                    'Preset with given name [%s] is not defined.',
                    $name
                )
            );
        }

        return $this->presets[$name];
    }

    public function getList(): array
    {
        return $this->presets;
    }
}
