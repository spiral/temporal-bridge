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
        private readonly WorkflowClientInterface $client,
        private readonly WorkflowReader $reader,
        private readonly ?DateInterval $defaultWorkflowExecutionTimeout = null,
        private readonly ?DateInterval $defaultWorkflowRunTimeout = null,
        private readonly ?DateInterval $defaultWorkflowTaskTimeout = null,
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

        if ($id !== null) {
            $options = $options->withWorkflowId($id);
        }

        if ($this->defaultWorkflowExecutionTimeout !== null) {
            $options = $options->withWorkflowExecutionTimeout(
                $this->defaultWorkflowExecutionTimeout
            );
        }

        if ($this->defaultWorkflowRunTimeout !== null) {
            $options = $options->withWorkflowRunTimeout(
                $this->defaultWorkflowRunTimeout
            );
        }

        if ($this->defaultWorkflowTaskTimeout !== null) {
            $options = $options->withWorkflowTaskTimeout(
                $this->defaultWorkflowTaskTimeout
            );
        }

        return $options;
    }

    /**
     * @param class-string $class
     * @throws \ReflectionException
     */
    private function getTypeFromWorkflowClass(string $class): string
    {
        return $this->reader->fromClass($class)->getID();
    }
}
