<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\DeclarationWorkerResolver;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\WorkersRegistryInterface;
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
            )
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

        $this->dispatcher->serve();
    }
}
