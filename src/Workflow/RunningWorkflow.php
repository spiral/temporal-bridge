<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Workflow;

use Temporal\Client\WorkflowStubInterface;

class RunningWorkflow
{
    public function __construct(
        private WorkflowStubInterface $workflow
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->workflow, $name], $arguments);
    }
}
