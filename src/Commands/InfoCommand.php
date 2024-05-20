<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;
use Spiral\TemporalBridge\Declaration\DeclarationType;
use Spiral\TemporalBridge\DeclarationRegistryInterface;
use Spiral\TemporalBridge\DeclarationWorkerResolver;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Internal\Declaration\Reader\ActivityReader;
use Temporal\Internal\Declaration\Reader\WorkflowReader;

#[AsCommand(
    name: 'temporal:info',
    description: 'Show information about registered temporal workflows and activities.',
)]
final class InfoCommand extends Command
{
    #[Option(name: 'show-activities', shortcut: 'a', description: 'Show activities.')]
    private bool $showActivities = false;

    public function perform(
        DeclarationRegistryInterface $registry,
        DeclarationWorkerResolver $workerResolver,
        WorkflowReader $workflowReader,
        ActivityReader $activityReader,
        DirectoriesInterface $dir,
    ): int {
        $workflows = [];
        $activities = [];

        foreach ($registry->getDeclarationList() as $declaration) {
            $taskQueue = $declaration->taskQueue === null
                ? $workerResolver->resolve($declaration->class)
                : [$declaration->taskQueue];

            if ($declaration->type === DeclarationType::Workflow) {
                $prototype = $workflowReader->fromClass($declaration->class->getName());
                $workflows[$prototype->getID()] = [
                    'class' => $declaration->class->getName(),
                    'file' => $declaration->class->getFileName(),
                    'name' => $prototype->getID(),
                    'task_queue' => \implode(', ', $taskQueue),
                ];
            } else {
                $taskQueueShown = false;

                foreach ($activityReader->fromClass($declaration->class->getName()) as $prototype) {
                    $activities[$declaration->class->getName()][$prototype->getID()] = [
                        'file' => $declaration->class->getFileName(),
                        'name' => $prototype->getID(),
                        'handler' => $declaration->class->getShortName() . '::' . $prototype->getHandler()->getName(),
                        'task_queue' => !$taskQueueShown ? \implode(', ', $taskQueue) : '',
                    ];

                    $taskQueueShown = true;
                }
            }
        }

        $rootDir = \realpath($dir->get('root')) . '/';

        \assert($this->output instanceof OutputInterface);

        $this->output->title('Workflows');

        $table = $this->table(['Name', 'Class', 'Task Queue']);
        foreach ($workflows as $workflow) {
            $table->addRow([
                \sprintf('<fg=green>%s</>', $workflow['name']),
                $workflow['class'] . "\n" . \sprintf(
                    '<fg=blue>%s</>',
                    self::normalizePath($rootDir, $workflow['file']),
                ),
                $workflow['task_queue'],
            ]);
        }
        $table->render();

        if (!$this->showActivities) {
            return self::SUCCESS;
        }

        $this->output->title('Activities');
        $table = $this->table(['Name', 'Class', 'Task Queue']);
        foreach ($activities as $class => $prototypes) {
            foreach ($prototypes as $prototype) {
                $table->addRow([
                    $prototype['name'],
                    $prototype['handler'],
                    $prototype['task_queue'],
                ]);
            }
            if (\end($activities) !== $prototypes) {
                $table->addRow(new TableSeparator());
            }
        }
        $table->render();

        return self::SUCCESS;
    }

    /**
     * @param non-empty-string $rootDir
     * @param non-empty-string $file
     */
    private static function normalizePath(string $rootDir, string $file): string
    {
        $file = \str_replace('\\', '/', $file);
        $rootDir = \str_replace('\\', '/', $rootDir);

        return \str_starts_with($file, $rootDir)
            ? \substr($file, \strlen($rootDir))
            : $file;
    }
}
