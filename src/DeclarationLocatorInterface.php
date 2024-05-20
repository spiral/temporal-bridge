<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

/**
 * @deprecated Use {@see DeclarationRegistryInterface} instead.
 */
interface DeclarationLocatorInterface
{
    /**
     * List of all declarations for workflows and activities.
     *
     * @return iterable<class-string<WorkflowInterface>|class-string<ActivityInterface>, \ReflectionClass>
     * @deprecated Use {@see DeclarationRegistryInterface::getDeclarationList} instead.
     */
    public function getDeclarations(): iterable;
}
