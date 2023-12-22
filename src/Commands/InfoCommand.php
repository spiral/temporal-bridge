<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\DeclarationWorkerResolver;
use Symfony\Component\Console\Helper\TableSeparator;
use Temporal\Internal\Declaration\Reader\ActivityReader;
use Temporal\Internal\Declaration\Reader\WorkflowReader;
use Temporal\Workflow\WorkflowInterface;

#[AsCommand(
    name: 'temporal:info',
    description: 'Show information about registered temporal workflows and activities.',
)]
final class InfoCommand extends Command
{
    public function perform(
        DeclarationLocatorInterface $locator,
        DeclarationWorkerResolver $workerResolver,
        WorkflowReader $workflowReader,
        ActivityReader $activityReader,
        DirectoriesInterface $dir,
    ): int {
        $workflows = [];
        $activities = [];

        foreach ($locator->getDeclarations() as $type => $declaration) {
            $taskQueue = $workerResolver->resolve($declaration);

            if ($type === WorkflowInterface::class) {
                $prototype = $workflowReader->fromClass($declaration->getName());
                $workflows[$prototype->getID()] = [
                    'class' => $declaration->getName(),
                    'file' => $declaration->getFileName(),
                    'name' => $prototype->getID(),
                    'task_queue' => $taskQueue,
                ];
            } else {
                foreach ($activityReader->fromClass($declaration->getName()) as $prototype) {
                    $activities[$declaration->getName()][$prototype->getID()] = [
                        'file' => $declaration->getFileName(),
                        'name' => $prototype->getID(),
                        'handler' => $declaration->getShortName() . '::' . $prototype->getHandler()->getName(),
                        'task_queue' => $taskQueue,
                    ];
                }
            }
        }

        $rootDir = \realpath($dir->get('root')) . '/';

        $this->output->title('Workflows');
        $table = $this->table(['Name', 'Class', 'Task Queue']);
        foreach ($workflows as $workflow) {
            $table->addRow([
                \sprintf('<fg=green>%s</>', $workflow['name']),
                $workflow['class'] . "\n" . \sprintf('<fg=blue>%s</>', \str_replace($rootDir, '', $workflow['file'])),
                $workflow['task_queue'],
            ]);
        }
        $table->render();


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
}
