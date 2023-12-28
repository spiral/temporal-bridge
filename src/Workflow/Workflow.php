<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Workflow;

use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Client\WorkflowProxy;
use Temporal\Internal\Support\DateInterval;

/**
 * @psalm-template T of object
 * @internal
 * @psalm-import-type DateIntervalValue from DateInterval
 */
final class Workflow
{
    private ?WorkflowSignal $signal = null;
    private ?RetryOptions $retryOptions = null;

    /**
     * @param class-string<T> $class
     */
    public function __construct(
        private readonly WorkflowClientInterface $client,
        private WorkflowOptions $options,
        private readonly string $class,
        private readonly string $type,
    ) {
    }

    public function getId(): ?string
    {
        return $this->options->workflowId;
    }

    public function backoffRetryCoefficient(float $coefficient): self
    {
        $this->retryOptions = $this->getRetryOptions()
            ->withBackoffCoefficient($coefficient);

        return $this;
    }

    /**
     * @param positive-int $attempts
     */
    public function maxRetryAttempts(int $attempts): self
    {
        $this->retryOptions = $this->getRetryOptions()
            ->withMaximumAttempts($attempts);

        return $this;
    }

    /**
     * @param DateIntervalValue|null $interval
     */
    public function maxRetryInterval($interval): self
    {
        $this->retryOptions = $this->getRetryOptions()
            ->withMaximumInterval($interval);

        return $this;
    }

    /**
     * @param DateIntervalValue|null $interval
     */
    public function initialRetryInterval($interval): self
    {
        $this->retryOptions = $this->getRetryOptions()
            ->withInitialInterval($interval);

        return $this;
    }

    /**
     * Workflow id to use when starting. If not specified a UUID is generated.
     * Note that it is dangerous as in case of client side retries no
     * deduplication will happen based on the generated id. So prefer assigning
     * business meaningful ids if possible.
     */
    public function assignId(string $id, ?int $policy = null): self
    {
        if ($policy !== null) {
            $this->withWorkflowIdReusePolicy($policy);
        }

        return $this->withWorkflowId($id);
    }

    /**
     * Sends signal on start.
     * @param mixed ...$args
     */
    public function withSignal(string $name, ...$args): self
    {
        $this->signal = new WorkflowSignal($name, $args);

        return $this;
    }

    /**
     * Starts untyped and typed workflow stubs in async mode.
     * @param mixed ...$args
     */
    public function run(...$args): RunningWorkflow
    {
        if ($this->retryOptions && ! $this->options->retryOptions) {
            $this->withRetryOptions($this->retryOptions);
        }

        $workflow = $this->createStub();

        if ($this->signal) {
            $run = $this->client->startWithSignal(
                workflow: $workflow,
                signal: $this->signal->getName(),
                signalArgs: $this->signal->getArgs(),
                startArgs: $args
            );
        } else {
            $run = $this->client->start($workflow, ...$args);
        }

        return new RunningWorkflow(
            client: $this->client,
            workflow: $this->client->newUntypedRunningWorkflowStub(
                workflowID: $run->getExecution()->getID(),
                runID: $run->getExecution()->getRunID(),
                workflowType: $this->type
            ),
            id: $run->getExecution()->getID(),
            class: $this->class
        );
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'with')) {
            if (method_exists($this->options, $name)) {
                $this->options = call_user_func_array([$this->options, $name], $arguments);

                return $this;
            }
        }

        if (method_exists($this->class, $name)) {
            return call_user_func_array([$this->createStub(), $name], $arguments);
        }

        throw new \BadMethodCallException(\sprintf('Method [%s] doesn\'t exist.', $name));
    }

    /**
     * @return T
     */
    private function createStub(): WorkflowProxy
    {
        return $this->client->newWorkflowStub($this->class, $this->options);
    }

    private function getRetryOptions(): RetryOptions
    {
        if ($this->retryOptions) {
            return $this->retryOptions;
        }

        return $this->retryOptions = new RetryOptions();
    }
}
