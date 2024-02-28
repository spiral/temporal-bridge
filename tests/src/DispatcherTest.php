<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Mockery as m;
use Spiral\Attributes\AttributeReader;
use Spiral\Framework\Spiral;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\DeclarationWorkerResolver;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\Tests\App\SomeActivity;
use Spiral\TemporalBridge\Tests\App\SomeActivityWithDefaultWorker;
use Spiral\TemporalBridge\Tests\App\SomeActivityWithScope;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\Tests\App\SomeWorkflowWithMultipleWorkers;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Workflow\WorkflowInterface;

final class DispatcherTest extends TestCase
{
    private Dispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = new Dispatcher(
            RoadRunnerMode::Temporal,
            $this->getContainer(),
            new DeclarationWorkerResolver(
                new AttributeReader(),
                new TemporalConfig(['defaultWorker' => 'foo']),
            ),
            $this->getContainer(),
        );
    }

    public function testServeWithoutDeclarations(): void
    {
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

        $this->dispatcher->serve();
    }

    public function testServeWithDeclarations(): void
    {
        $locator = $this->mockContainer(DeclarationLocatorInterface::class);
        $locator->shouldReceive('getDeclarations')->once()->andReturnUsing(function () {
            yield WorkflowInterface::class => new \ReflectionClass(SomeWorkflow::class);
            yield WorkflowInterface::class => new \ReflectionClass(SomeWorkflowWithMultipleWorkers::class);
            yield ActivityInterface::class => new \ReflectionClass(SomeActivity::class);
            yield ActivityInterface::class => new \ReflectionClass(SomeActivityWithDefaultWorker::class);
        });

        $registry = $this->mockContainer(WorkersRegistryInterface::class);

        $registry
            ->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn($worker = m::mock(WorkerInterface::class));

        $worker->shouldReceive('registerActivity')->once()->withSomeOfArgs(SomeActivityWithDefaultWorker::class);

        $registry
            ->shouldReceive('get')
            ->twice()
            ->with('worker2')
            ->andReturn($worker = m::mock(WorkerInterface::class));

        $worker->shouldReceive('registerWorkflowTypes')->once()->with(SomeWorkflow::class);
        $worker->shouldReceive('registerWorkflowTypes')->once()->with(SomeWorkflowWithMultipleWorkers::class);

        $registry
            ->shouldReceive('get')
            ->twice()
            ->with('worker1')
            ->andReturn($worker = m::mock(WorkerInterface::class));

        $worker->shouldReceive('registerWorkflowTypes')->once()->with(SomeWorkflowWithMultipleWorkers::class);
        $worker->shouldReceive('registerActivity')->once()->withSomeOfArgs(SomeActivity::class);


        $factory = $this->mockContainer(WorkerFactoryInterface::class);
        $factory->shouldReceive('run')->once();

        $this->dispatcher->serve();
    }

    public function testScope(): void
    {
        $binder = $this->getContainer()->getBinder(Spiral::TemporalActivity);
        $binder->bind(SomeActivityWithScope::class, SomeActivityWithScope::class);
        $binder->bind(\ArrayAccess::class, $this->createMock(\ArrayAccess::class));

        $ref = new \ReflectionMethod($this->dispatcher, 'makeActivity');

        $this->assertInstanceOf(
            SomeActivityWithScope::class,
            $ref->invoke($this->dispatcher, new \ReflectionClass(SomeActivityWithScope::class))
        );
    }
}
