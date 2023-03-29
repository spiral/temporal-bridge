<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Tests\App\SomeInterceptor;
use Spiral\TemporalBridge\WorkerFactory;
use Temporal\Exception\ExceptionInterceptor;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Internal\Interceptor\PipelineProvider;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactory;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;

final class WorkerFactoryTest extends TestCase
{
    private TemporalWorkerFactory $temporalWorkerFactory;

    protected function setUp(): void
    {
        $this->temporalWorkerFactory = $this->createMock(TemporalWorkerFactory::class);
    }

    public function testCreateWithoutAnyOptions(): void
    {
        $this->temporalWorkerFactory
            ->expects($this->once())
            ->method('newWorker')
            ->with('without-any-options')
            ->willReturn($worker = $this->createMock(WorkerInterface::class));

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        $this->assertSame($worker, $factory->create('without-any-options'));
    }

    public function testCreateWithOptionsAsValue(): void
    {
        $this->temporalWorkerFactory
            ->expects($this->once())
            ->method('newWorker')
            ->with('with-options-as-value', $this->equalTo(WorkerOptions::new()->withEnableSessionWorker()))
            ->willReturn($worker = $this->createMock(WorkerInterface::class));

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        $this->assertSame($worker, $factory->create('with-options-as-value'));
    }

    public function testCreateWithOptionsInArray(): void
    {
        $this->temporalWorkerFactory
            ->expects($this->once())
            ->method('newWorker')
            ->with('with-options-in-array', $this->equalTo(WorkerOptions::new()->withEnableSessionWorker()))
            ->willReturn($worker = $this->createMock(WorkerInterface::class));

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        $this->assertSame($worker, $factory->create('with-options-in-array'));
    }

    /**
     * @dataProvider exceptionInterceptorsDataProvider
     */
    public function testCreateWithExceptionInterceptor(string $name): void
    {
        $this->temporalWorkerFactory
            ->expects($this->once())
            ->method('newWorker')
            ->with($name, null, $this->equalTo(new ExceptionInterceptor([])))
            ->willReturn($worker = $this->createMock(WorkerInterface::class));

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        $this->assertSame($worker, $factory->create($name));
    }

    public function testCreateWithInterceptors(): void
    {
        $expectedInterceptors = new SimplePipelineProvider([
            new SomeInterceptor(),
            new SomeInterceptor(),
            new SomeInterceptor()
        ]);

        $this->temporalWorkerFactory
            ->expects($this->once())
            ->method('newWorker')
            ->with('with-interceptors', null, null, $this->equalTo($expectedInterceptors))
            ->willReturn($worker = $this->createMock(WorkerInterface::class));

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        $this->assertSame($worker, $factory->create('with-interceptors'));
    }

    public function testCreateWithAllOptions(): void
    {
        $expectedInterceptors = new SimplePipelineProvider([
            new SomeInterceptor(),
            new SomeInterceptor(),
            new SomeInterceptor()
        ]);

        $this->temporalWorkerFactory
            ->expects($this->once())
            ->method('newWorker')
            ->with(
                'all',
                $this->equalTo(WorkerOptions::new()->withEnableSessionWorker()),
                $this->equalTo(new ExceptionInterceptor([])),
                $this->equalTo($expectedInterceptors)
            )
            ->willReturn($worker = $this->createMock(WorkerInterface::class));

        $factory = $this->createWorkerFactory($this->temporalWorkerFactory);

        $this->assertSame($worker, $factory->create('all'));
    }

    public function exceptionInterceptorsDataProvider(): \Traversable
    {
        yield ['with-exception-interceptor-as-string'];
        yield ['with-exception-interceptor-as-autowire'];
        yield ['with-exception-interceptor-as-instance'];
    }

    private function createWorkerFactory(TemporalWorkerFactory $workerFactory): WorkerFactory
    {
        $container = new Container();
        $container->bind(PipelineProvider::class, SimplePipelineProvider::class);
        $container->bind(ExceptionInterceptor::class, new ExceptionInterceptor([]));

        $interceptors = [
            SomeInterceptor::class,
            new SomeInterceptor(),
            new Autowire(SomeInterceptor::class)
        ];

        return new WorkerFactory(
            $workerFactory,
            $this->createMock(FinalizerInterface::class),
            $container,
            new TemporalConfig([
                'workers' => [
                    'with-options-as-value' => WorkerOptions::new()->withEnableSessionWorker(),
                    'with-options-in-array' => [
                        'options' => WorkerOptions::new()->withEnableSessionWorker()
                    ],
                    'with-interceptors' => [
                        'interceptors' => $interceptors
                    ],
                    'with-exception-interceptor-as-string' => [
                        'exception_interceptor' => ExceptionInterceptor::class
                    ],
                    'with-exception-interceptor-as-autowire' => [
                        'exception_interceptor' => new Autowire(ExceptionInterceptor::class, [])
                    ],
                    'with-exception-interceptor-as-instance' => [
                        'exception_interceptor' => new ExceptionInterceptor([])
                    ],
                    'all' => [
                        'options' => WorkerOptions::new()->withEnableSessionWorker(),
                        'interceptors' => $interceptors,
                        'exception_interceptor' => ExceptionInterceptor::class
                    ]
                ]
            ])
        );
    }
}
