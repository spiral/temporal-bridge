<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

interface ActivityFactoryInterface
{
    /**
     * Make an activity instance
     */
    public function make(\ReflectionClass $class): object;
}
