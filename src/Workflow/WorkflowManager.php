<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Workflow;

use DateInterval;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Internal\Declaration\Reader\WorkflowReader;

class WorkflowManager implements WorkflowManagerInterface
{
    public function __construct(
        private WorkflowClientInterface $client,
        private WorkflowReader $reader,
        private ?DateInterval $defaultWorkflowExecutionTimeout = null,
        private ?DateInterval $defaultWorkflowRunTimeout = null,
        private ?DateInterval $defaultWorkflowTaskTimeout = null,
    ) {
    }

    public function create(
        string $class,
        ?string $id = null
    ): Workflow {
        return new Workflow(
            $this->client,
            $this->createOptions($id),
            $class,
            $this->getTypeFromWorkflowClass($class)
        );
    }

    public function createScheduled(string $class, string $expression, ?string $id = null): Workflow
    {
        return $this->create($class, $id)
            ->withCronSchedule($expression);
    }

    public function getById(
        string $id,
        ?string $class = null,
    ): RunningWorkflow {
        $type = $class !== null ? $this->getTypeFromWorkflowClass($class) : null;

        return new RunningWorkflow(
            $this->client->newUntypedRunningWorkflowStub(
                workflowID: $id,
                workflowType: $type
            )
        );
    }

    private function createOptions(?string $id): WorkflowOptions
    {
        $options = new WorkflowOptions();

        if ($id) {
            $options = $options->withWorkflowId($id);
        }

        if ($this->defaultWorkflowExecutionTimeout) {
            $options = $options->withWorkflowExecutionTimeout(
                $this->defaultWorkflowExecutionTimeout
            );
        }

        if ($this->defaultWorkflowRunTimeout) {
            $options = $options->withWorkflowRunTimeout(
                $this->defaultWorkflowExecutionTimeout
            );
        }

        if ($this->defaultWorkflowTaskTimeout) {
            $options = $options->withWorkflowTaskTimeout(
                $this->defaultWorkflowExecutionTimeout
            );
        }

        return $options;
    }

    private function getTypeFromWorkflowClass(string $class): string
    {
        return $this->reader->fromClass($class)->getID();
    }
}
