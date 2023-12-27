<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

interface DeclarationLocatorInterface
{
    /**
     * List of all declarations for workflows and activities.
     *
     * @return iterable<class-string<WorkflowInterface>|class-string<ActivityInterface>, \ReflectionClass>
     */
    public function getDeclarations(): iterable;
}
