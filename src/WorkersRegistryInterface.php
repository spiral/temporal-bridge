<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Temporal\Worker\WorkerInterface;

interface WorkersRegistryInterface
{
    public function get(\ReflectionClass $declaration): WorkerInterface;
}
