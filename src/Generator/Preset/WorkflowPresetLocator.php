<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator\Preset;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\FactoryInterface;
use Spiral\TemporalBridge\Attribute\WorkflowPreset;
use Spiral\Tokenizer\ClassesInterface;

class WorkflowPresetLocator implements WorkflowPresetLocatorInterface
{
    public function __construct(
        private FactoryInterface $factory,
        private ClassesInterface $classes,
        private ReaderInterface $reader,
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

                $presets[$attr->name] = $this->factory->make($class->getName());
            }
        }

        return $presets;
    }
}
