<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Preset;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_CLASS), NamedArgumentConstructor]
class WorkflowPreset
{
    public function __construct(
        public string $name
    ) {
    }
}
