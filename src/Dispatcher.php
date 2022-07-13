<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use ReflectionClass;
use Spiral\Boot\DispatcherInterface;
use Spiral\Core\Container;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Temporal\Activity\ActivityInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Workflow\WorkflowInterface;

final class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly RoadRunnerMode $mode,
        private readonly Container $container
    ) {
    }

    public function canServe(): bool
    {
        return \PHP_SAPI === 'cli' && $this->mode === RoadRunnerMode::Temporal;
    }

    public function serve(): void
    {
        // finds all available workflows, activity types and commands in a given directory
        /**
         * @var array<class-string<WorkflowInterface>|class-string<ActivityInterface>, ReflectionClass> $declarations
         */
        $declarations = $this->container->get(DeclarationLocatorInterface::class)->getDeclarations();

        // factory initiates and runs task queue specific activity and workflow workers
        $factory = $this->container->get(WorkerFactoryInterface::class);

        $registry = $this->container->get(WorkersRegistryInterface::class);

        foreach ($declarations as $type => $declaration) {
            // Worker that listens on a task queue and hosts both workflow and activity implementations.
            $worker = $registry->get($declaration);

            if ($type === WorkflowInterface::class) {
                // Workflows are stateful. So you need a type to create instances.
                $worker->registerWorkflowTypes($declaration->getName());
            }

            if ($type === ActivityInterface::class) {
                // Workflows are stateful. So you need a type to create instances.
                $worker->registerActivity(
                    $declaration->getName(),
                    fn(ReflectionClass $class) => $this->container->make($class->getName())
                );
            }
        }

        // start primary loop
        $factory->run();
    }
}
