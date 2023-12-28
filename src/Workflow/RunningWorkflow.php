<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Workflow;

use Spiral\TemporalBridge\Exception\WorkflowNotFoundException;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowStubInterface;
use Temporal\Internal\Client\WorkflowProxy;

/**
 * @template T
 * @mixin WorkflowStubInterface
 * @internal
 */
final class RunningWorkflow
{
    /**
     * @param non-empty-string $id
     * @param class-string<T>|null $class
     */
    public function __construct(
        private WorkflowClientInterface $client,
        private WorkflowStubInterface $workflow,
        private string $id,
        private ?string $class
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        return \call_user_func_array([$this->workflow, $name], $arguments);
    }

    /**
     * @param class-string<T>|null $class
     * @return WorkflowProxy|T
     * @throws WorkflowNotFoundException
     */
    public function getWorkflow(?string $class = null): object
    {
        $class ??= $this->class;

        if ($class === null) {
            throw new WorkflowNotFoundException('Workflow class should be defined.');
        }

        return $this->client->newRunningWorkflowStub($class, $this->id);
    }
}
