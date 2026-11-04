<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Mockery as m;
use Spiral\Attributes\AttributeReader;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Declaration\DeclarationDto;
use Spiral\TemporalBridge\Declaration\DeclarationType;
use Spiral\TemporalBridge\DeclarationRegistryInterface;
use Spiral\TemporalBridge\DeclarationWorkerResolver;
use Spiral\TemporalBridge\Dispatcher;
use Spiral\TemporalBridge\Tests\App\SomeActivity;
use Spiral\TemporalBridge\Tests\App\SomeActivityWithDefaultWorker;
use Spiral\TemporalBridge\Tests\App\SomeActivityWithScope;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\Tests\App\SomeWorkflowWithMultipleWorkers;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Temporal\Worker\WorkerFactoryInterface;
use Temporal\Worker\WorkerInterface;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class DispatcherTest extends TestCase
{
    private Dispatcher $dispatcher;

    public function testCanServe(): void
    {
        Assert::true(Dispatcher::canServe(RoadRunnerMode::Temporal));

        Assert::false(Dispatcher::canServe(RoadRunnerMode::Http));
        Assert::false(Dispatcher::canServe(RoadRunnerMode::Jobs));
        Assert::false(Dispatcher::canServe(RoadRunnerMode::Grpc));
        Assert::false(Dispatcher::canServe(RoadRunnerMode::Centrifuge));
        Assert::false(Dispatcher::canServe(RoadRunnerMode::Unknown));
    }

    public function testServeWithoutDeclarations(): void
    {
        $locator = $this->mockContainer(DeclarationRegistryInterface::class);
        $locator->shouldReceive('getDeclarationList')->once()->andReturn([]);

        $registry = $this->mockContainer(WorkersRegistryInterface::class);
        $registry
            ->shouldReceive('get')
            ->once()
            ->with(WorkerFactoryInterface::DEFAULT_TASK_QUEUE)
            ->andReturn(m::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $factory = $this->mockContainer(WorkerFactoryInterface::class);
        $factory->shouldReceive('run')->once();

        $this->dispatcher->serve();
    }

    public function testServeWithDeclarations(): void
    {
        $locator = $this->mockContainer(DeclarationRegistryInterface::class);
        $locator->shouldReceive('getDeclarationList')->once()->andReturnUsing(static function () {
            yield new DeclarationDto(DeclarationType::Workflow, new \ReflectionClass(SomeWorkflow::class));
            yield new DeclarationDto(DeclarationType::Workflow, new \ReflectionClass(SomeWorkflowWithMultipleWorkers::class));
            yield new DeclarationDto(DeclarationType::Activity, new \ReflectionClass(SomeActivity::class));
            yield new DeclarationDto(DeclarationType::Activity, new \ReflectionClass(SomeActivityWithDefaultWorker::class));
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
        $binder = $this->getContainer()->getBinder('temporal-activity');
        $binder->bind(SomeActivityWithScope::class, SomeActivityWithScope::class);
        $binder->bind(\ArrayAccess::class, m::mock(\ArrayAccess::class)->shouldIgnoreMissing());

        $ref = new \ReflectionMethod($this->dispatcher, 'makeActivity');

        Assert::instanceOf($ref->invoke($this->dispatcher, new \ReflectionClass(SomeActivityWithScope::class)), SomeActivityWithScope::class);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->dispatcher = new Dispatcher(
            $this->getContainer(),
            new DeclarationWorkerResolver(
                new AttributeReader(),
                new TemporalConfig(['defaultWorker' => 'foo']),
            ),
            $this->getContainer(),
        );
    }
}
