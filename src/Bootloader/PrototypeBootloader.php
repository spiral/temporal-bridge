<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Prototype\Bootloader\PrototypeBootloader as BasePrototypeBootloader;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Temporal\Client\WorkflowClientInterface;

class PrototypeBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        BasePrototypeBootloader::class,
    ];

    public function boot(BasePrototypeBootloader $prototype): void
    {
        $prototype->bindProperty('workflow', WorkflowClientInterface::class);
        $prototype->bindProperty('workflow-manager', WorkflowManagerInterface::class);
    }
}
