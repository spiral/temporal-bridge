<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Mockery;
use Mockery\MockInterface;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Console\Console;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\TemporalBridge\Tests\App\TestApp;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Testo\Assert;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;

/**
 * Boots a fresh application before every test.
 *
 * Testo keeps one case instance for all tests of a class, so the application and the environment
 * are reset after each test.
 */
abstract class TestCase
{
    /**
     * Environment of the next application; filled by {@see Testing\Env}.
     *
     * @var array<non-empty-string, mixed>
     */
    public array $env = [];

    private ?TestApp $app = null;

    /**
     * Runs before the hooks of subclasses, which may already use the container.
     */
    #[BeforeTest(priority: 1000)]
    public function bootApplication(): void
    {
        $root = $this->rootDirectory();
        $environment = new Environment($this->env);

        $app = TestApp::create(
            directories: [
                'root' => $root,
                'app' => $root . '/app',
                'runtime' => $root . '/runtime',
                'cache' => $root . '/runtime/cache',
            ],
            handleErrors: false,
        )->withBootloaders($this->defineBootloaders());

        $app->getContainer()->removeBinding(EnvironmentInterface::class);
        $app->getContainer()->bindSingleton(EnvironmentInterface::class, $environment);
        $app->run($environment);

        $this->app = $app;
        (new \ReflectionClass(ContainerScope::class))->setStaticPropertyValue('container', $app->getContainer());
    }

    #[AfterTest]
    public function shutdownApplication(): void
    {
        $this->app = null;
        $this->env = [];
        (new \ReflectionClass(ContainerScope::class))->setStaticPropertyValue('container', null);
    }

    protected function rootDirectory(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * @return array<class-string>
     */
    protected function defineBootloaders(): array
    {
        return [
            \Spiral\Boot\Bootloader\ConfigurationBootloader::class,
            \Spiral\Bootloader\Attributes\AttributesBootloader::class,
            \Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader::class,
            \Spiral\TemporalBridge\Bootloader\PrototypeBootloader::class,
        ];
    }

    protected function getContainer(): Container
    {
        return $this->app?->getContainer() ?? throw new \LogicException('The application is not booted.');
    }

    /**
     * Replaces the container binding with a Mockery mock.
     *
     * @param class-string $alias
     */
    protected function mockContainer(string $alias): MockInterface
    {
        $this->getContainer()->removeBinding($alias);
        $this->getContainer()->bindSingleton($alias, $mock = \Mockery::mock($alias));

        return $mock;
    }

    /**
     * @return string The command output.
     */
    protected function runCommand(string $command, array $args = []): string
    {
        $input = new ArrayInput($args);
        $input->setInteractive(false);
        $output = new BufferedOutput();

        $this->getContainer()->get(Console::class)->run($command, $input, $output);

        return $output->fetch();
    }

    /**
     * @return object The resolved binding.
     */
    protected function assertContainerBound(string $alias, ?string $class = null): object
    {
        Assert::true(
            $this->getContainer()->hasBinding($alias),
            \sprintf('Container does not contain entry with name [%s].', $alias),
        );

        $object = $this->getContainer()->get($alias);
        Assert::instanceOf($object, $class ?? $alias);

        return $object;
    }

    protected function assertContainerBoundAsSingleton(string $alias, string $class): void
    {
        Assert::same(
            $this->getContainer()->get($alias),
            $this->assertContainerBound($alias, $class),
            \sprintf('Container [%s] is bound, but it contains not a singleton.', $alias),
        );
    }
}
