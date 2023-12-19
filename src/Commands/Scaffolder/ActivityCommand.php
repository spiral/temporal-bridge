<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands\Scaffolder;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Command\AbstractCommand;
use Spiral\TemporalBridge\Scaffolder\Declaration\ActivityDeclaration;
use Temporal\Worker\WorkerFactoryInterface;

#[AsCommand(name: 'create:activity', description: 'Create workflow activity declaration')]
final class ActivityCommand extends AbstractCommand
{
    #[Argument(description: 'Activity class name')]
    #[Question(question: 'What would you like to name the Activity class?')]
    public string $name;

    #[Option(shortcut: 'w', description: 'Optional worker to assign')]
    public string $worker = WorkerFactoryInterface::DEFAULT_TASK_QUEUE;

    #[Option(name: 'activity-name', shortcut: 'a', description: 'Optional activity name')]
    public ?string $activityName = null;

    #[Option(name: 'method', shortcut: 'm', description: 'Optional activity name')]
    public array $methods = [];

    public function perform(): int
    {
        $declaration = $this->createDeclaration(ActivityDeclaration::class, [
            'activityName' => $this->activityName,
        ]);

        if ($this->worker !== WorkerFactoryInterface::DEFAULT_TASK_QUEUE) {
            $declaration->assignWorker($this->worker);
        }

        foreach ($this->methods as $method) {
            if (\str_contains($method, ':')) {
                $array = \explode(separator: ':', string: $method, limit: 2);
                \assert(\count($array) === 2);
                [$method, $type] = $array;
            } else {
                $type = 'mixed';
            }

            $declaration->addMethod($method, $type);
        }

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
