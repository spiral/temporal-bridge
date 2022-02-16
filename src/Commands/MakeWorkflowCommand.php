<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Command;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Generator\WorkFlowGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeWorkflowCommand extends Command
{
    protected const NAME = 'temporal:make-workflow';
    protected const DESCRIPTION = 'Make a new Temporal workflow';

    public function perform(TemporalConfig $config, WorkFlowGenerator $generator, DirectoriesInterface $dirs): int
    {
        $name = $this->getNameInput();
        $namespace = $this->getNamespaceFromClass($name) ?? $config->getDefaultNamespace();
        $className = $this->qualifyClass($name, $namespace);
        $namespace = $namespace.'\\'.$className;

        $generator
            ->withNamespace($namespace)
            ->withMethodParameters($this->getParametersInputWithType((array)$this->option('param')))
            ->withMethod($this->option('method'))
            ->withSignalMethods((array)$this->option('signal'))
            ->withQueryMethods($this->getParametersInputWithType((array)$this->option('query')));

        $generator->generate(
            className: $className,
            path: $this->getPath($namespace, $dirs->get('app')),
        );

        return self::SUCCESS;
    }


    protected function getPath(string $namespace, string $appDir): string
    {
        if (str_starts_with($namespace, 'App')) {
            $namespace = str_replace('App', 'src', $namespace);
        }

        return $appDir.str_replace('\\', '/', $namespace).'/';
    }

    private function getParametersInputWithType(array $parameters): array
    {
        $params = [];

        foreach ($parameters as $param) {
            [$param, $type] = explode(':', $param, 2);
            $type ??= 'string';
            $params[$param] = $type;
        }

        return $params;
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
}
