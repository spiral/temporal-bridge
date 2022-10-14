<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Mockery as m;
use Spiral\Attributes\AttributeReader;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Spiral\TemporalBridge\ActivityFactoryInterface;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\Tests\App\SimpleWorkflow;
use Spiral\TemporalBridge\Tests\App\SomeActivity;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Workflow\WorkflowInterface;

final class DispatcherTest extends TestCase
{
    public function testResolvingQueueName(): void
    {
        $dispatcher = new Dispatcher(
            RoadRunnerMode::Temporal,
            new AttributeReader(),
            new TemporalConfig(['defaultWorker' => 'foo']),
            $this->getContainer(),
        );

        $ref = new \ReflectionClass($dispatcher);
        $method = $ref->getMethod('resolveQueueName');
        $method->setAccessible(true);

        $queue = $method->invoke(
            $dispatcher,
            new \ReflectionClass(ActivityInterfaceWithAttribute::class)
        );
        $this->assertSame('worker1', $queue);

        $queue = $method->invoke(
            $dispatcher,
            new \ReflectionClass(ActivityInterfaceWithoutAttribute::class)
        );
        $this->assertSame('foo', $queue);
    }

    public function testRegisteringWorkflowsAndActivities(): void
    {
        $container = $this->getContainer();

        $container->bind(
            DeclarationLocatorInterface::class,
            $declarations = m::mock(DeclarationLocatorInterface::class)
        );

        $container->bind(
            WorkersRegistryInterface::class,
            $registry = m::mock(WorkersRegistryInterface::class)
        );

        $container->bind(
            WorkerFactoryInterface::class,
            $factory = m::mock(WorkerFactoryInterface::class)
        );

        $container->bind(
            ActivityFactoryInterface::class,
            $activityFactory = m::mock(ActivityFactoryInterface::class)
        );

        $activityFactory->shouldReceive('make')
            ->once()
            ->withArgs(fn(\ReflectionClass $class) => $class->getName() === SomeActivity::class);

        $factory->shouldReceive('run')->once();

        $registry->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($worker = m::mock(WorkerInterface::class));

        $registry->shouldReceive('get')
            ->once()
            ->with('worker1')
            ->andReturn($worker1 = m::mock(WorkerInterface::class));

        $worker->shouldReceive('registerWorkflowTypes')
            ->once()
            ->with(SimpleWorkflow::class);

        $worker1->shouldReceive('registerActivity')
            ->once()
            ->withArgs(function (string $class, \Closure $factory) {
                $factory(new \ReflectionClass($class));
                return $class === SomeActivity::class;
            });

        $declarations->shouldReceive('getDeclarations')
            ->once()
            ->andReturn([
                WorkflowInterface::class => new \ReflectionClass(SimpleWorkflow::class),
                ActivityInterface::class => new \ReflectionClass(SomeActivity::class),
            ]);

        $dispatcher = new Dispatcher(
            RoadRunnerMode::Temporal,
            new AttributeReader(),
            new TemporalConfig(['defaultWorker' => 'foo']),
            $container,
        );

        $dispatcher->serve();
    }
}

#[AssignWorker(name: 'worker1')]
interface ActivityInterfaceWithAttribute
{
}


interface ActivityInterfaceWithoutAttribute
{
}
