<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\TemporalBridge\Workflow\RunningWorkflow;
use Spiral\TemporalBridge\Workflow\Workflow;
use Temporal\Client\WorkflowOptions;
use Temporal\Client\WorkflowStubInterface;

interface WorkflowManagerInterface
{
    /**
     * @psalm-template T of object
     * @param class-string<T> $class
     * @return RunningWorkflow|T|WorkflowStubInterface
     */
    public function getById(
        string $id,
        ?string $class = null,
    ): RunningWorkflow;

    /**
     * @psalm-template T of object
     * @param class-string<T> $class
     * @return T|Workflow|WorkflowOptions
     */
    public function create(
        string $class,
        ?string $id = null
    ): Workflow;

    /**
     * @psalm-template T of object
     * @param class-string<T> $class
     * @return T|Workflow|WorkflowOptions
     */
    public function createScheduled(
        string $class,
        string $expression,
        ?string $id = null
    ): Workflow;
}
