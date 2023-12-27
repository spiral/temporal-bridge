<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Workflow\WorkflowInterface;

final class DispatcherTest extends TestCase
{
    private \ReflectionMethod $method;
    private Dispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = new Dispatcher(
            RoadRunnerMode::Temporal,
            new AttributeReader(),
            $this->getContainer(),
        );

        $ref = new \ReflectionClass($this->dispatcher);
        $this->method = $ref->getMethod('resolveQueueName');
        $this->method->setAccessible(true);
    }

    public function testResolvingQueueNameWithAttributeOnClass(): void
    {
        $queue = $this->method->invoke(
            $this->dispatcher,
            new \ReflectionClass(ActivityInterfaceWithAttribute::class),
        );

        $this->assertSame('worker1', $queue);
    }

    public function testResolvingQueueNameWithAttributeOnParentClass(): void
    {
        $queue = $this->method->invoke(
            $this->dispatcher,
            new \ReflectionClass(ActivityClass::class),
        );

        $this->assertSame('worker1', $queue);
    }

    public function testResolvingQueueNameWithoutAttribute(): void
    {
        $queue = $this->method->invoke(
            $this->dispatcher,
            new \ReflectionClass(ActivityInterfaceWithoutAttribute::class),
        );

        $this->assertNull($queue);
    }

    public function testServeWithoutDeclarations(): void
    {
        $dispatcher = new Dispatcher(
            RoadRunnerMode::Temporal,
            new AttributeReader(),
            $this->getContainer(),
        );

        $locator = $this->mockContainer(DeclarationLocatorInterface::class);
        $locator->shouldReceive('getDeclarations')->once()->andReturn([]);

        $registry = $this->mockContainer(WorkersRegistryInterface::class);
        $registry
            ->shouldReceive('get')
            ->once()
            ->with(WorkerFactoryInterface::DEFAULT_TASK_QUEUE)
            ->andReturn($this->createMock(WorkerInterface::class));

        $factory = $this->mockContainer(WorkerFactoryInterface::class);
        $factory->shouldReceive('run')->once();

        $dispatcher->serve();
    }

    public function testServeWithDeclarations(): void
    {
        $dispatcher = new Dispatcher(
            RoadRunnerMode::Temporal,
            new AttributeReader(),
            $this->getContainer(),
        );

        $locator = $this->mockContainer(DeclarationLocatorInterface::class);
        $locator->shouldReceive('getDeclarations')->once()->andReturn([
            WorkflowInterface::class => new \ReflectionClass(SomeWorkflow::class),
        ]);

        $registry = $this->mockContainer(WorkersRegistryInterface::class);
        $registry
            ->shouldReceive('get')
            ->once()
            ->with('worker2')
            ->andReturn($this->createMock(WorkerInterface::class));

        $factory = $this->mockContainer(WorkerFactoryInterface::class);
        $factory->shouldReceive('run')->once();

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

class ActivityClass implements ActivityInterfaceWithAttribute
{
}
