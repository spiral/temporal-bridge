<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Core\FactoryInterface;

final class ContainerActivityFactory implements ActivityFactoryInterface
{
    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    public function make(\ReflectionClass $class): object
    {
        return $this->factory->make($class->getName());
    }
}
