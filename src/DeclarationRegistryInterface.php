<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

interface DeclarationRegistryInterface extends DeclarationLocatorInterface
{
    /**
     * Add a new declaration to the registry.
     *
     * @param \ReflectionClass|class-string $class Workflow or activity class name or reflection.
     */
    public function addDeclaration(\ReflectionClass|string $class): void;
}
