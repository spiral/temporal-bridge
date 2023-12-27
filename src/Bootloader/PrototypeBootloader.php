<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Prototype\Bootloader\PrototypeBootloader as BasePrototypeBootloader;
use Temporal\Client\WorkflowClientInterface;

final class PrototypeBootloader extends Bootloader
{
    public function defineDependencies(): array
    {
        return [
            BasePrototypeBootloader::class,
        ];
    }

    public function boot(BasePrototypeBootloader $prototype): void
    {
        $prototype->bindProperty('workflow', WorkflowClientInterface::class);
    }
}
