<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Container;
use Spiral\RoadRunner\Environment\Mode;
use Spiral\Boot\EnvironmentInterface;
use Spiral\TemporalBridge\Attribute\RegisterWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Temporal\Activity\ActivityInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Workflow\WorkflowInterface;

final class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private EnvironmentInterface $env,
        private Container $container
    ) {
    }

    public function canServe(): bool
    {
        return \PHP_SAPI === 'cli' && $this->env->get('RR_MODE', '') === Mode::MODE_TEMPORAL;
    }

    public function serve(): void
    {
        // finds all available workflows, activity types and commands in a given directory
        /** @var array<class-string<WorkflowInterface>|class-string<ActivityInterface>, ReflectionClass> $declarations */
        $declarations = $this->container->get(DeclarationLocatorInterface::class)->getDeclarations();

        // factory initiates and runs task queue specific activity and workflow workers
        $factory = $this->container->get(WorkerFactoryInterface::class);

        $finalizer = $this->container->get(FinalizerInterface::class);
        $registry = $this->container->get(WorkersRegistryInterface::class);
        $defaultWorker = $this->container->get(TemporalConfig::class)->getDefaultWorker();
        $reader = $this->container->get(ReaderInterface::class);

        foreach ($declarations as $type => $declaration) {
            $registerWorker = $reader->firstClassMetadata($declaration, RegisterWorker::class);

            // Worker that listens on a task queue and hosts both workflow and activity implementations.
            $worker = $registry->get($registerWorker === null ? $defaultWorker : $registerWorker->name);

            $worker->registerActivityFinalizer(fn() => $finalizer->finalize());

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
