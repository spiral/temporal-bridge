<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Bootloader;

use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader as BaseScaffolderBootloader;
use Spiral\TemporalBridge\Commands\Scaffolder\ActivityCommand;
use Spiral\TemporalBridge\Commands\Scaffolder\WorkflowCommand;
use Spiral\TemporalBridge\Scaffolder\Declaration\ActivityDeclaration;
use Spiral\TemporalBridge\Scaffolder\Declaration\WorkflowDeclaration;

final class ScaffolderBootloader extends Bootloader
{
    public function defineDependencies(): array
    {
        return [
            ConsoleBootloader::class,
            BaseScaffolderBootloader::class,
        ];
    }

    public function init(BaseScaffolderBootloader $scaffolder, ConsoleBootloader $console): void
    {
        $this->configureCommands($console);
        $this->configureDeclarations($scaffolder);
    }

    private function configureCommands(ConsoleBootloader $console): void
    {
        $console->addCommand(WorkflowCommand::class);
        $console->addCommand(ActivityCommand::class);
    }

    private function configureDeclarations(BaseScaffolderBootloader $scaffolder): void
    {
        $scaffolder->addDeclaration(WorkflowDeclaration::TYPE, [
            'namespace' => 'Endpoint\\Temporal\\Workflow',
            'postfix' => 'Workflow',
            'class' => WorkflowDeclaration::class,
        ]);

        $scaffolder->addDeclaration(ActivityDeclaration::TYPE, [
            'namespace' => 'Endpoint\\Temporal\\Activity',
            'postfix' => 'Activity',
            'class' => ActivityDeclaration::class,
        ]);
    }
}
