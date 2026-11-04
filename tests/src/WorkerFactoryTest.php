<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Tests\App\SomeInterceptor;
use Spiral\TemporalBridge\WorkerFactory;
use Temporal\Exception\ExceptionInterceptor;
use Temporal\Interceptor\PipelineProvider;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactory;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class WorkerFactoryTest
{
    private TemporalWorkerFactory $temporalWorkerFactory;

    public static function exceptionInterceptorsDataProvider(): \Traversable
    {
        yield ['with-exception-interceptor-as-string'];
        yield ['with-exception-interceptor-as-autowire'];
        yield ['with-exception-interceptor-as-instance'];
    }

    public function testCreateWithoutAnyOptions(): void
    {
        $this->temporalWorkerFactory->shouldReceive('newWorker')->once()->with('without-any-options', \Mockery::andAnyOtherArgs())->andReturn($worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        Assert::same($factory->create('without-any-options'), $worker);
    }

    public function testCreateWithOptionsAsValue(): void
    {
        $this->temporalWorkerFactory->shouldReceive('newWorker')->once()->with('with-options-as-value', \Mockery::isEqual(WorkerOptions::new()->withEnableSessionWorker()), \Mockery::andAnyOtherArgs())->andReturn($worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        Assert::same($factory->create('with-options-as-value'), $worker);
    }

    public function testCreateWithOptionsInArray(): void
    {
        $this->temporalWorkerFactory->shouldReceive('newWorker')->once()->with('with-options-in-array', \Mockery::isEqual(WorkerOptions::new()->withEnableSessionWorker()), \Mockery::andAnyOtherArgs())->andReturn($worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        Assert::same($factory->create('with-options-in-array'), $worker);
    }

    #[DataProvider('exceptionInterceptorsDataProvider')]
    public function testCreateWithExceptionInterceptor(string $name): void
    {
        $this->temporalWorkerFactory->shouldReceive('newWorker')->once()->with($name, null, \Mockery::isEqual(new ExceptionInterceptor([])), \Mockery::andAnyOtherArgs())->andReturn($worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        Assert::same($factory->create($name), $worker);
    }

    public function testCreateWithInterceptors(): void
    {
        $expectedInterceptors = new SimplePipelineProvider([
            new SomeInterceptor(),
            new SomeInterceptor(),
            new SomeInterceptor(),
        ]);

        $this->temporalWorkerFactory->shouldReceive('newWorker')->once()->with('with-interceptors', null, null, \Mockery::isEqual($expectedInterceptors), \Mockery::andAnyOtherArgs())->andReturn($worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory, $expectedInterceptors);

        Assert::same($factory->create('with-interceptors'), $worker);
    }

    public function testCreateWithAllOptions(): void
    {
        $expectedInterceptors = new SimplePipelineProvider([
            new SomeInterceptor(),
            new SomeInterceptor(),
            new SomeInterceptor(),
        ]);

        $this->temporalWorkerFactory->shouldReceive('newWorker')->once()->with('all', \Mockery::isEqual(WorkerOptions::new()->withEnableSessionWorker()), \Mockery::isEqual(new ExceptionInterceptor([])), \Mockery::isEqual($expectedInterceptors), \Mockery::andAnyOtherArgs())->andReturn($worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory, $expectedInterceptors);

        Assert::same($factory->create('all'), $worker);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->temporalWorkerFactory = \Mockery::mock(TemporalWorkerFactory::class)->shouldIgnoreMissing();
    }

    private function createWorkerFactory(
        TemporalWorkerFactory $workerFactory,
        PipelineProvider $pipelineProvider = new SimplePipelineProvider(),
    ): WorkerFactory {
        $container = new Container();
        $container->bind(PipelineProvider::class, SimplePipelineProvider::class);
        $container->bind(ExceptionInterceptor::class, new ExceptionInterceptor([]));

        $interceptors = [
            SomeInterceptor::class,
            new SomeInterceptor(),
            new Autowire(SomeInterceptor::class),
        ];

        return new WorkerFactory(
            $workerFactory,
            \Mockery::mock(FinalizerInterface::class)->shouldIgnoreMissing(),
            $container,
            $pipelineProvider,
            new TemporalConfig([
                'workers' => [
                    'with-options-as-value' => WorkerOptions::new()->withEnableSessionWorker(),
                    'with-options-in-array' => [
                        'options' => WorkerOptions::new()->withEnableSessionWorker(),
                    ],
                    'with-interceptors' => [
                        'interceptors' => $interceptors,
                    ],
                    'with-exception-interceptor-as-string' => [
                        'exception_interceptor' => ExceptionInterceptor::class,
                    ],
                    'with-exception-interceptor-as-autowire' => [
                        'exception_interceptor' => new Autowire(ExceptionInterceptor::class, []),
                    ],
                    'with-exception-interceptor-as-instance' => [
                        'exception_interceptor' => new ExceptionInterceptor([]),
                    ],
                    'all' => [
                        'options' => WorkerOptions::new()->withEnableSessionWorker(),
                        'interceptors' => $interceptors,
                        'exception_interceptor' => ExceptionInterceptor::class,
                    ],
                ],
            ]),
        );
    }
}
