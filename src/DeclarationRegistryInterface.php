<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\TemporalBridge\Declaration\DeclarationDto;

interface DeclarationRegistryInterface
{
    /**
     * Add a new declaration to the registry.
     *
     * @param \ReflectionClass|class-string $class Workflow or activity class name or reflection.
     */
    public function addDeclaration(DeclarationDto|\ReflectionClass|string $class): void;

    /**
     * List all declarations.
     *
     * @return iterable<DeclarationDto>
     */
    public function getDeclarationList(): iterable;
}
