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

    public function getById(
        string $id,
        ?string $runID = null,
        ?string $class = null,
    ): RunningWorkflow {
        $type = $class !== null ? $this->reader->fromClass($class)->getID() : null;

        return new RunningWorkflow(
            $this->client->newUntypedRunningWorkflowStub($id, $runID, $type)
        );
    }

    public function create(
        string $class,
        ?string $id = null
    ): Workflow {
        $workflow = $this->reader->fromClass($class);

        return new Workflow(
            $this->client,
            $this->createOptions($id),
            $class,
            $workflow->getID()
        );
    }

    public function createScheduled(string $class, string $expression, ?string $id = null): Workflow
    {
        return $this->create($class, $id)
            ->withCronSchedule((string)$expression);
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
}
