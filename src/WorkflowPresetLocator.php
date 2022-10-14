<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\FactoryInterface;
use Spiral\TemporalBridge\Preset\PresetInterface;
use Spiral\TemporalBridge\Preset\WorkflowPreset;
use Spiral\Tokenizer\ClassesInterface;

class WorkflowPresetLocator implements WorkflowPresetLocatorInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly ClassesInterface $classes,
        private readonly ReaderInterface $reader,
    ) {
    }

    public function getPresets(): array
    {
        $presets = [];

        foreach ($this->classes->getClasses() as $class) {
            if ($attr = $this->reader->firstClassMetadata($class, WorkflowPreset::class)) {
                if (! $class->implementsInterface(PresetInterface::class)) {
                    continue;
                }

                $preset = $this->factory->make($class->getName());
                \assert($preset instanceof PresetInterface);

                $presets[$attr->name] = $preset;
            }
        }

        return $presets;
    }
}
