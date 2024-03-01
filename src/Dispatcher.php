<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\Attribute\DispatcherScope;
use Spiral\Boot\DispatcherInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Temporal\Activity\ActivityInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Workflow\WorkflowInterface;

#[DispatcherScope('temporal')]
final class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly DeclarationWorkerResolver $workerResolver,
        private readonly ScopeInterface $scope,
    ) {
    }

    public static function canServe(RoadRunnerMode $mode): bool
    {
        return \PHP_SAPI === 'cli' && $mode === RoadRunnerMode::Temporal;
    }

    public function serve(): void
    {
        // finds all available workflows, activity types and commands in a given directory
        /**
         * @var array<class-string<WorkflowInterface>|class-string<ActivityInterface>, ReflectionClass> $declarations
         */
        $declarations = $this->container->get(DeclarationLocatorInterface::class)->getDeclarations();

        // factory initiates and runs task queue specific activity and workflow workers
        /** @var WorkerFactoryInterface $factory */
        $factory = $this->container->get(WorkerFactoryInterface::class);
        /** @var WorkersRegistryInterface $registry */
        $registry = $this->container->get(WorkersRegistryInterface::class);

        $hasDeclarations = false;
        foreach ($declarations as $type => $declaration) {
            // Worker that listens on a task queue and hosts both workflow and activity implementations.
            $taskQueues = $this->workerResolver->resolve($declaration);

            foreach ($taskQueues as $taskQueue) {
                $worker = $registry->get($taskQueue);

                if ($type === WorkflowInterface::class) {
                    // Workflows are stateful. So you need a type to create instances.
                    $worker->registerWorkflowTypes($declaration->getName());
                }

                if ($type === ActivityInterface::class) {
                    // Workflows are stateful. So you need a type to create instances.
                    $worker->registerActivity($declaration->getName(), $this->makeActivity(...));
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

    private function makeActivity(ReflectionClass $class): object
    {
        /** @psalm-suppress InvalidArgument */
        return $this->scope->runScope(
            new Scope('temporal.activity'),
            static fn(FactoryInterface $factory): object => $factory->make($class->getName()),
        );
    }
}
