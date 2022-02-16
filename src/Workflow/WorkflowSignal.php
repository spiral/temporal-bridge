<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Workflow;

class WorkflowSignal
{
    public function __construct(
        private string $name,
        private array $args = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}
