<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands\Scaffolder;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Command\AbstractCommand;
use Spiral\TemporalBridge\Scaffolder\Declaration\WorkflowDeclaration;
use Temporal\Worker\WorkerFactoryInterface;

#[AsCommand(name: 'create:workflow', description: 'Create workflow declaration')]
final class WorkflowCommand extends AbstractCommand
{
    #[Argument(description: 'Workflow class name')]
    #[Question(question: 'What would you like to name the Workflow class?')]
    public string $name;

    #[Option(shortcut: 'w', description: 'Optional worker to assign')]
    public string $worker = WorkerFactoryInterface::DEFAULT_TASK_QUEUE;

    #[Option(name: 'workflow-name', shortcut: 'a', description: 'Optional workflow name')]
    public ?string $workflowName = null;

    #[Option(name: 'query-method', shortcut: 'c', description: 'Create query method in a format "name:type"')]
    public array $queryMethods = [];

    #[Option(name: 'signal-method', shortcut: 's', description: 'Create signal method in a format "name"')]
    public array $signalMethods = [];

    public function perform(): int
    {
        $declaration = $this->createDeclaration(WorkflowDeclaration::class, [
            'workflowName' => $this->workflowName,
        ]);

        if ($this->worker !== WorkerFactoryInterface::DEFAULT_TASK_QUEUE) {
            $declaration->assignWorker($this->worker);
        }

        foreach ($this->queryMethods as $method) {
            if (\str_contains($method, ':')) {
                $array = \explode(separator: ':', string: $method, limit: 2);
                \assert(\count($array) === 2);
                [$method, $type] = $array;
            } else {
                $type = 'mixed';
            }

            $declaration->addQueryMethod($method, $type);
        }

        foreach ($this->signalMethods as $name) {
            $declaration->addSignalMethod($name);
        }

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
