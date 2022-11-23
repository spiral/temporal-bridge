<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\Utils;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

trait WithContext
{
    protected function defineOptions(): array
    {
        return [
            ['with-handler', null, InputOption::VALUE_NONE, 'Generate handler classes'],
            ['with-activity', null, InputOption::VALUE_NONE, 'Generate activity classes'],
            ['scheduled', null, InputOption::VALUE_NONE, 'With scheduling by cron'],
            ['queue', null, InputOption::VALUE_OPTIONAL, 'Set task queue'],
            ['method', 'm', InputOption::VALUE_OPTIONAL, 'Set method name', 'handle'],
            ['query', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'With additional query methods'],
            [
                'activity',
                'a',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'With additional activity methods',
            ],
            [
                'signal',
                's',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'With additional signal methods',
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

    public function verifyExistsWorkflow(Context $context): bool
    {
        if (! \is_dir($context->getPath()) || $this->option('force')) {
            return false;
        }

        $question = new QuestionHelper();

        \assert($this->output instanceof OutputInterface);
        \assert($this->input instanceof InputInterface);

        return ! $question->ask(
            $this->input,
            $this->output,
            new ConfirmationQuestion(
                \sprintf(
                    '<error>Workflow with given name [%s] exists. Some files can be overwritten. Would you like to continue?</error>',
                    $context->getBaseClass()
                )
            )
        );
    }

    public function getContext(): Context
    {
        \assert($this->container instanceof ContainerInterface);

        $config = $this->container->get(TemporalConfig::class);
        \assert($config instanceof TemporalConfig);

        $dirs = $this->container->get(DirectoriesInterface::class);
        \assert($dirs instanceof DirectoriesInterface);

        $name = $this->getNameInput();
        $namespace = $this->getNamespaceFromClass($name) ?? $config->getDefaultNamespace();
        $className = $this->qualifyClass($name, $namespace);
        $namespace = $namespace.'\\'.$className;

        $context = (new Context(
            $dirs->get('app').'src/Workflow/'.$className.'/',
            $namespace,
            $className,
        ))
            ->withActivityMethods(Utils::parseMethods((array)$this->option('activity')))
            ->withMethodParameters(Utils::parseParameters((array)$this->option('param')))
                ->withHandlerMethod($this->option('method') ?? 'handle');

        if ($this->input->hasOption('query') && $this->option('query')) {
            $context->withQueryMethods(Utils::parseMethods((array)$this->option('query')));
        }

        if ($this->option('scheduled') ?? false) {
            $context->withCronSchedule();
        }

        if ($this->option('with-handler') ?? false) {
            $context->withHandler();
        }

        if ($this->option('with-activity') ?? false) {
            $context->withActivity();
        }

        if ($this->option('queue') ?? false) {
            $context->withTaskQueue($this->option('queue'));
        }

        return $context;
    }

    protected function getPath(string $namespace, string $appDir): string
    {
        if (\str_starts_with($namespace, 'App')) {
            $namespace = \str_replace('App', 'src', $namespace);
        }

        return $appDir.\str_replace('\\', '/', $namespace).'/';
    }

    private function getNameInput(): string
    {
        return \trim($this->argument('name'));
    }

    private function getNamespaceFromClass(string $name): ?string
    {
        $namespace = \trim(\implode('\\', \array_slice(\explode('\\', $name), 0, -1)), '\\');

        return ! empty($namespace) ? $namespace : null;
    }

    private function qualifyClass(string $name, string $namespace): string
    {
        $name = \str_replace('/', '\\', $name);
        $name = \str_replace(['-', '_', '.'], ' ', $name);
        $name = \str_replace(' ', '', $name);
        if (\str_starts_with($name, $namespace)) {
            $name = \str_replace($namespace, '', $name);
        }

        $name = \ltrim($name, '\\/');

        return \ucwords($name);
    }
}
