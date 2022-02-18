<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\RoadRunner\EnvironmentInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Workflow\WorkflowInterface;

final class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private EnvironmentInterface $env,
        private ContainerInterface $container
    ) {
    }

    public function canServe(): bool
    {
        return \PHP_SAPI === 'cli' && $this->env->getMode() === Mode::MODE_TEMPORAL;
    }

    public function serve()
    {
        // finds all available workflows, activity types and commands in a given directory
        $declarations = $this->container->get(DeclarationLocatorInterface::class);

        // factory initiates and runs task queue specific activity and workflow workers
        $factory = $this->container->get(WorkerFactoryInterface::class);

        // Worker that listens on a task queue and hosts both workflow and activity implementations.
        $worker = $factory->newWorker();

        foreach ($declarations->getDeclarations() as $type => $declaration) {
            if ($type === WorkflowInterface::class) {
                // Workflows are stateful. So you need a type to create instances.
                $worker->registerWorkflowTypes($declaration->getName());
            }

            if ($type === ActivityInterface::class) {
                // Workflows are stateful. So you need a type to create instances.
                $worker->registerActivity($declaration);
            }
        }

        // start primary loop
        $factory->run();
    }
}
