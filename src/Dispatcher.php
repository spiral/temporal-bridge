<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use ReflectionClass;
use Spiral\Boot\DispatcherInterface;
use Spiral\Core\Container;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Spiral\TemporalBridge\Declaration\DeclarationDto;
use Spiral\TemporalBridge\Declaration\DeclarationType;
use Temporal\Worker\WorkerFactoryInterface;

final class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly RoadRunnerMode $mode,
        private readonly Container $container,
        private readonly DeclarationWorkerResolver $workerResolver,
    ) {
    }

    public function canServe(): bool
    {
        return \PHP_SAPI === 'cli' && $this->mode === RoadRunnerMode::Temporal;
    }

    public function serve(): void
    {
        // finds all available workflows, activity types and commands in a given directory
        /** @var list<DeclarationDto> $declarations */
        $declarations = $this->container->get(DeclarationRegistryInterface::class)->getDeclarationList();

        // factory initiates and runs task queue specific activity and workflow workers
        /** @var WorkerFactoryInterface $factory */
        $factory = $this->container->get(WorkerFactoryInterface::class);
        /** @var WorkersRegistryInterface $registry */
        $registry = $this->container->get(WorkersRegistryInterface::class);

        $hasDeclarations = false;
        foreach ($declarations as $declaration) {
            // Worker that listens on a task queue and hosts both workflow and activity implementations.
            $taskQueues = $this->workerResolver->resolve($declaration->class);

            foreach ($taskQueues as $taskQueue) {
                $worker = $registry->get($taskQueue);

                if ($declaration->type === DeclarationType::Workflow) {
                    // Workflows are stateful. So you need a type to create instances.
                    $worker->registerWorkflowTypes($declaration->class->getName());
                }

                if ($declaration->type === DeclarationType::Activity) {
                    // Workflows are stateful. So you need a type to create instances.
                    $worker->registerActivity(
                        $declaration->class->getName(),
                        fn(ReflectionClass $class): object => $this->container->make($class->getName()),
                    );
                }

                $hasDeclarations = true;
            }
        }

        if (!$hasDeclarations) {
            $registry->get(WorkerFactoryInterface::DEFAULT_TASK_QUEUE);
        }

        // start primary loop
        $factory->run();
    }
}
