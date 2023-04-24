<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Console\Command;
use Spiral\TemporalBridge\Generator\ActivityGenerator;
use Spiral\TemporalBridge\Generator\ActivityInterfaceGenerator;
use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\Generator;
use Spiral\TemporalBridge\Generator\HandlerGenerator;
use Spiral\TemporalBridge\Generator\HandlerInterfaceGenerator;
use Spiral\TemporalBridge\Generator\WorkflowGenerator;
use Spiral\TemporalBridge\Generator\WorkflowInterfaceGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

final class MakeWorkflowCommand extends Command
{
    use WithContext;

    protected const NAME = 'temporal:make-workflow';
    protected const DESCRIPTION = 'Make a new Temporal workflow';
    protected const ARGUMENTS = [
        ['name', InputArgument::REQUIRED, 'Workflow name'],
    ];

    public function perform(Generator $generator): int
    {
        $context = $this->getContext();

        if ($this->verifyExistsWorkflow($context)) {
            return self::SUCCESS;
        }

        \assert($this->output instanceof OutputInterface);

        $generator->generate(
            $this->output,
            $context,
            $this->defineGenerators($context)
        );

        return self::SUCCESS;
    }

    private function defineGenerators(Context $context): array
    {
        $generators = [
            'WorkflowInterface' => new WorkflowInterfaceGenerator(),
            'Workflow' => new WorkflowGenerator(),
        ];

        if ($context->hasActivity()) {
            $generators = \array_merge($generators, [
                'ActivityInterface' => new ActivityInterfaceGenerator(),
                'Activity' => new ActivityGenerator(),
            ]);
        }

        if ($context->hasHandler()) {
            $generators = \array_merge($generators, [
                'HandlerInterface' => new HandlerInterfaceGenerator(),
                'Handler' => new HandlerGenerator(),
            ]);
        }

        return $generators;
    }
}
