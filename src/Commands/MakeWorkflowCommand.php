<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Command;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Generator\ActivityGenerator;
use Spiral\TemporalBridge\Generator\ActivityInterfaceGenerator;
use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\Generator;
use Spiral\TemporalBridge\Generator\HandlerGenerator;
use Spiral\TemporalBridge\Generator\HandlerInterfaceGenerator;
use Spiral\TemporalBridge\Generator\SignalWorkflowGenerator;
use Spiral\TemporalBridge\Generator\Utils;
use Spiral\TemporalBridge\Generator\WorkflowGenerator;
use Spiral\TemporalBridge\Generator\WorkflowInterfaceGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeWorkflowCommand extends Command
{
    protected const NAME = 'temporal:make-workflow';
    protected const DESCRIPTION = 'Make a new Temporal workflow';

    public function perform(
        TemporalConfig $config,
        Generator $generator,
        DirectoriesInterface $dirs
    ): int {
        $name = $this->getNameInput();
        $namespace = $this->getNamespaceFromClass($name) ?? $config->getDefaultNamespace();
        $className = $this->qualifyClass($name, $namespace);
        $namespace = $namespace.'\\'.$className;

        $context = (new Context())
            ->withClassBaseName($className)
            ->withRootDirectory($this->getPath($namespace, $dirs->get('app')))
            ->withNamespace($namespace)
            ->withMethodParameters(Utils::parseParameters((array)$this->option('param')))
            ->withMethod($this->option('method'))
            ->withSignalMethods((array)$this->option('signal'))
            ->withQueryMethods(Utils::parseParameters((array)$this->option('query')));

        if ($this->option('scheduled')) {
            $context = $context->withCronSchedule();
        }

        $generator->generate($context, $this->defineGenerators());

        return self::SUCCESS;
    }


    protected function getPath(string $namespace, string $appDir): string
    {
        if (str_starts_with($namespace, 'App')) {
            $namespace = str_replace('App', 'src', $namespace);
        }

        return $appDir.str_replace('\\', '/', $namespace).'/';
    }

    private function getNameInput(): string
    {
        return trim($this->argument('name'));
    }

    private function getNamespace(): string
    {
        $rootNamespace = 'App\\Workflow\\';

        return trim($rootNamespace, '\\');
    }

    private function getNamespaceFromClass(string $name): ?string
    {
        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        return ! empty($namespace) ? $namespace : null;
    }

    private function qualifyClass(string $name, string $namespace): string
    {
        $name = str_replace('/', '\\', $name);
        $name = str_replace(['-', '_', '.'], ' ', $name);
        $name = str_replace(' ', '', $name);
        if (str_starts_with($name, $namespace)) {
            $name = str_replace($namespace, '', $name);
        }

        $name = ltrim($name, '\\/');

        return ucwords($name);
    }

    protected const ARGUMENTS = [
        ['name', InputArgument::REQUIRED, 'Workflow name'],
    ];

    protected const OPTIONS = [
        ['with-handler', null, InputOption::VALUE_NONE, 'Generate handler classes'],
        ['scheduled', null, InputOption::VALUE_NONE, 'With scheduling by cron'],
        ['method', 'm', InputOption::VALUE_OPTIONAL, 'With method name', 'handle'],
        ['query', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'With query methods'],
        ['signal', 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'With signal methods'],
        [
            'param',
            'p',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'With params',
            ['name:string'],
        ],
    ];

    private function defineGenerators(): array
    {
        $generators = [
            'WorkflowInterface' => new WorkflowInterfaceGenerator(),
            'Workflow' => new SignalWorkflowGenerator(),
        ];

        if (\count($this->option('signal')) === 0) {
            $generators = \array_merge($generators, [
                'ActivityInterface' => new ActivityInterfaceGenerator(),
                'Activity' => new ActivityGenerator(),
                'Workflow' => new WorkflowGenerator(),
            ]);
        }

        if ($this->option('with-handler')) {
            $generators = \array_merge($generators, [
                'HandlerInterface' => new HandlerInterfaceGenerator(),
                'Handler' => new HandlerGenerator(),
            ]);
        }

        return $generators;
    }
}
