<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator\Preset;

use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\FileGeneratorInterface;

interface PresetInterface
{
    /**
     * Preset description
     */
    public function getDescription(): ?string;

    /**
     * @return FileGeneratorInterface[]
     */
    public function generators(Context $context): array;
}
