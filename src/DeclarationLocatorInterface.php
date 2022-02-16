<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

interface DeclarationLocatorInterface
{
    public function getDeclarations(): iterable;
}
