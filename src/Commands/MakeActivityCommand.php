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
use Symfony\Component\Console\Input\InputOption;

final class MakeActivityCommand extends Command
{
    use WithContext;

    protected function defineOptions(): array
    {
        return [
            ['queue', 't', InputOption::VALUE_OPTIONAL, 'Set task queue'],
            ['method', 'm', InputOption::VALUE_OPTIONAL, 'Set method name', 'handle'],
            [
                'activity',
                'a',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'With additional activity methods',
            ],
            [
                'param',
                'p',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'With additional params',
                ['name:string'],
            ],
            ['force', null, InputOption::VALUE_NONE, 'Generate workflow with overwriting exist files'],
        ];
    }

    protected const NAME = 'temporal:make-activity';
    protected const DESCRIPTION = 'Make a new Temporal activity class';

    public function perform(Generator $generator): int
    {
        $context = $this->getContext();

        if ($this->verifyExistsWorkflow($context)) {
            return self::SUCCESS;
        }

        $generator->generate(
            $this->output,
            $context,
            $this->defineGenerators($context)
        );

        return self::SUCCESS;
    }

    protected const ARGUMENTS = [
        ['name', InputArgument::REQUIRED, 'Activity name'],
    ];

    private function defineGenerators(Context $context): array
    {
        return [
            'ActivityInterface' => new ActivityInterfaceGenerator(),
            'Activity' => new ActivityGenerator(),
        ];
    }
}
