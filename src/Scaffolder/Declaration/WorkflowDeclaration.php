<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Scaffolder\Declaration;

use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration\AbstractDeclaration;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Temporal\Workflow\QueryMethod;
use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

final class WorkflowDeclaration extends AbstractDeclaration
{
    public const TYPE = 'workflow';

    public function __construct(
        ScaffolderConfig $config,
        string $name,
        ?string $comment = null,
        ?string $namespace = null,
        private ?string $workflowName = null,
    ) {
        parent::__construct($config, $name, $comment, $namespace);
    }

    public function declare(): void
    {
        $this->namespace->addUse(WorkflowInterface::class);
        $this->namespace->addUse(WorkflowMethod::class);

        $this->class->addAttribute(WorkflowInterface::class);

        $methodAttributeArgs = [];
        if ($this->workflowName !== null) {
            $methodAttributeArgs['name'] = $this->workflowName;
        }

        $this->class
            ->addMethod('handle')
            ->setPublic()
            ->setComment('Handle workflow')
            ->addAttribute(WorkflowMethod::class, $methodAttributeArgs)
            ->setBody('// TODO: Implement handle method');
    }

    public function assignWorker(string $worker): void
    {
        $this->namespace->addUse(AssignWorker::class);
        $this->class->addAttribute(AssignWorker::class, ['taskQueue' => $worker]);
    }

    public function addQueryMethod(string $name, string $returnType): void
    {
        $this->namespace->addUse(QueryMethod::class);
        $this->class
            ->addMethod($name)
            ->setPublic()
            ->addAttribute(QueryMethod::class)
            ->setReturnType($returnType)
            ->setBody('// TODO: Implement query method');
    }

    public function addSignalMethod(string $name): void
    {
        $this->namespace->addUse(SignalMethod::class);
        $this->class
            ->addMethod($name)
            ->setPublic()
            ->addAttribute(SignalMethod::class)
            ->setReturnType('void')
            ->setBody('// TODO: Implement signal method');
    }
}
