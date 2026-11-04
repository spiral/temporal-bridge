<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Bootloader;

use Mockery as m;
use Psr\Container\ContainerExceptionInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Tests\App\SomeInterceptor;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\Tests\Testing\Env;
use Spiral\TemporalBridge\WorkerFactory;
use Spiral\TemporalBridge\WorkerFactoryInterface;
use Spiral\TemporalBridge\WorkersRegistry;
use Spiral\TemporalBridge\WorkersRegistryInterface;
use Temporal\Api\Workflowservice\V1\WorkflowServiceClient;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\GRPC\ServiceClientInterface;
use Temporal\Client\ScheduleClient;
use Temporal\Client\ScheduleClientInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\Interceptor\PipelineProvider;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Internal\Interceptor\Interceptor;
use Temporal\Worker\WorkerFactoryInterface as TemporalWorkerFactoryInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\WorkerFactory as TemporalWorkerFactory;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class TemporalBridgeBootloaderTest extends TestCase
{
    public function testServiceClient(): void
    {
        $this->assertContainerBoundAsSingleton(
            ServiceClientInterface::class,
            ServiceClient::class,
        );
    }

    public function testScheduleClient(): void
    {
        $this->assertContainerBoundAsSingleton(
            ScheduleClientInterface::class,
            ScheduleClient::class,
        );
    }

    public function testTemporalWorkerFactory(): void
    {
        $this->assertContainerBoundAsSingleton(
            TemporalWorkerFactoryInterface::class,
            TemporalWorkerFactory::class,
        );
    }

    public function testWorkerFactory(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkerFactoryInterface::class,
            WorkerFactory::class,
        );
    }

    public function testDataConverter(): void
    {
        $this->assertContainerBoundAsSingleton(
            DataConverterInterface::class,
            DataConverter::class,
        );
    }

    public function testDeclarationLocator(): void
    {
        $this->assertContainerBoundAsSingleton(
            DeclarationLocatorInterface::class,
            DeclarationLocator::class,
        );
    }

    public function testWorkflowClient(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkflowClientInterface::class,
            WorkflowClient::class,
        );
    }

    public function testWorkersRegistry(): void
    {
        $this->assertContainerBoundAsSingleton(
            WorkersRegistryInterface::class,
            WorkersRegistry::class,
        );
    }

    public function testPipelineProvider(): void
    {
        $this->assertContainerBound(
            PipelineProvider::class,
            SimplePipelineProvider::class,
        );
    }

    public function testPipelineProviderResolvesConfiguredInterceptors(): void
    {
        $this->getContainer()->bindSingleton(TemporalConfig::class, new TemporalConfig([
            'interceptors' => [
                SomeInterceptor::class,
                new Autowire(SomeInterceptor::class),
                $instance = new SomeInterceptor(),
            ],
        ]));

        $provider = $this->assertContainerBound(PipelineProvider::class, SimplePipelineProvider::class);
        $interceptors = [...$this->fetchPrivateProperty($provider, 'interceptors')];

        Assert::count($interceptors, 3);
        Assert::instanceOf($interceptors[0], SomeInterceptor::class);
        Assert::instanceOf($interceptors[1], SomeInterceptor::class);
        Assert::notSame($interceptors[0], $interceptors[1]);
        Assert::same($interceptors[2], $instance);
    }

    #[Env('TEMPORAL_CONNECTION', 'default')]
    public function testConnection(): void
    {
        $client = $this->getContainer()->get(ServiceClientInterface::class);
        \assert($client instanceof ServiceClientInterface);
        $connection = $client->getConnection();
        $workflowService = $connection->getClient();
        \assert($workflowService instanceof WorkflowServiceClient);

        // It might be dns://localhost:7233
        Assert::string($workflowService->getTarget())->contains('localhost:7233');
    }

    #[Env('TEMPORAL_CONNECTION', 'ssl')]
    public function testSecureConnection(): void
    {
        $client = $this->getContainer()->get(ServiceClientInterface::class);
        \assert($client instanceof ServiceClientInterface);
        $connection = $client->getConnection();
        $workflowService = $connection->getClient();
        \assert($workflowService instanceof WorkflowServiceClient);

        // It might be dns://ssl:7233
        Assert::string($workflowService->getTarget())->contains('ssl:7233');
    }

    #[Env('TEMPORAL_CONNECTION', 'ssl')]
    public function testContext(): void
    {
        $client = $this->getContainer()->get(ServiceClientInterface::class);
        \assert($client instanceof ServiceClientInterface);
        $context = $client->getContext();

        Assert::same($context->getMetadata(), ['foo' => ['bar']]);
    }

    #[Env('TEMPORAL_CONNECTION', 'ssl')]
    public function testClientOptions(): void
    {
        $client = $this->getContainer()->get(WorkflowClientInterface::class);
        \assert($client instanceof WorkflowClientInterface);

        $clientOptions = $this->fetchPrivateProperty($client, 'clientOptions');
        \assert($clientOptions instanceof ClientOptions);

        Assert::same($clientOptions->namespace, 'foo-bar');
    }

    #[Env('TEMPORAL_CONNECTION', 'test')]
    public function testNonExistsConnection(): void
    {
        Expect::exception(\InvalidArgumentException::class)->withMessageContaining('Temporal client config `test` is not defined.');

        try {
            $this->getContainer()->get(ServiceClientInterface::class);
        } catch (ContainerExceptionInterface $e) {
            // Newer spiral/core wraps factory errors into a container exception.
            throw $e->getPrevious() ?? $e;
        }
    }

    public function testAddWorkerOptions(): void
    {
        $configs = new ConfigManager(m::mock(LoaderInterface::class)->shouldIgnoreMissing());
        $configs->setDefaults(TemporalConfig::CONFIG, ['workers' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $this->getContainer());
        $bootloader->addWorkerOptions('first', $first = WorkerOptions::new());
        $bootloader->addWorkerOptions('second', $second = WorkerOptions::new());

        Assert::same($configs->getConfig(TemporalConfig::CONFIG)['workers'], ['first' => $first, 'second' => $second]);
    }

    public function testAddInterceptor(): void
    {
        $configs = new ConfigManager(m::mock(LoaderInterface::class)->shouldIgnoreMissing());
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $this->getContainer());

        $bootloader->addInterceptor($iterceptor = m::mock(Interceptor::class));

        Assert::same($configs->getConfig(TemporalConfig::CONFIG)['interceptors'], [$iterceptor]);
    }

    public function testStringableInterceptor(): void
    {
        $configs = new ConfigManager(m::mock(LoaderInterface::class)->shouldIgnoreMissing());
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $factory = m::mock(FactoryInterface::class));

        $factory->shouldReceive('make')->with('foo')->andReturn($iterceptor = m::mock(Interceptor::class));

        $bootloader->addInterceptor('foo');

        Assert::same($configs->getConfig(TemporalConfig::CONFIG)['interceptors'], [$iterceptor]);
    }

    public function testAutowireInterceptor(): void
    {
        $configs = new ConfigManager(m::mock(LoaderInterface::class)->shouldIgnoreMissing());
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $factory = m::mock(FactoryInterface::class));

        $factory->shouldReceive('make')->with('foo', ['bar' => 'baz'])->andReturn($iterceptor = m::mock(Interceptor::class));

        $bootloader->addInterceptor(new Autowire('foo', ['bar' => 'baz']));

        Assert::same($configs->getConfig(TemporalConfig::CONFIG)['interceptors'], [$iterceptor]);
    }

    public function testInvalidInterceptor(): void
    {
        Expect::exception(\InvalidArgumentException::class)->withMessageContaining('Interceptor must be an instance of `Temporal\Internal\Interceptor\Interceptor`, `stdClass` given.');

        $configs = new ConfigManager(m::mock(LoaderInterface::class)->shouldIgnoreMissing());
        $configs->setDefaults(TemporalConfig::CONFIG, ['interceptors' => []]);

        $bootloader = new TemporalBridgeBootloader($configs, $factory = m::mock(FactoryInterface::class));

        $factory->shouldReceive('make')->with('foo')->andReturn(new \StdClass());

        $bootloader->addInterceptor('foo');
    }

    /**
     * @param non-empty-string $property
     */
    private function fetchPrivateProperty(object $object, string $property): mixed
    {
        return (static fn() => $object->$property)->bindTo(null, $object)();
    }
}
