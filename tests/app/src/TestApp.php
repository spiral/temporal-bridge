<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\Exception\BootException;
use Spiral\Core\Container;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

/**
 * Test kernel. Scaffolder commands take its namespace as the root namespace of generated classes.
 */
final class TestApp extends AbstractKernel
{
    protected const SYSTEM = [
        CoreBootloader::class,
        TokenizerBootloader::class,
    ];

    /** @var array<class-string> */
    private array $bootloaders = [];

    /**
     * @param array<class-string> $bootloaders
     */
    public function withBootloaders(array $bootloaders): self
    {
        $this->bootloaders = $bootloaders;
        return $this;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    protected function defineBootloaders(): array
    {
        return $this->bootloaders;
    }

    protected function bootstrap(): void {}

    protected function mapDirectories(array $directories): array
    {
        isset($directories['root']) or throw new BootException('Missing required directory `root`');
        $directories['app'] ??= $directories['root'] . '/app/';

        return \array_merge(
            [
                'public' => $directories['root'] . '/public/',
                'vendor' => $directories['root'] . '/vendor/',
                'runtime' => $directories['root'] . '/runtime/',
                'cache' => $directories['root'] . '/runtime/cache/',
                'config' => $directories['app'] . '/config/',
                'resources' => $directories['app'] . '/resources/',
            ],
            $directories,
        );
    }
}
