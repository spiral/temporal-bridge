<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Preset;

use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\FileGeneratorInterface;

interface PresetInterface
{
    /**
     * Preset description
     */
    public function getDescription(): ?string;

    public function init(Context $context): void;

    /**
     * @return FileGeneratorInterface[]
     */
    public function generators(Context $context): array;
}
