<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Acceptance\Support;

/**
 * A throwaway copy of the test application in a temporary directory.
 *
 * Commands run through the real console entry point `tests/app/app.php` in a separate PHP process,
 * with the temporary directory as the working directory and a minimal environment.
 */
final class Application
{
    private const ENTRY_POINT = __DIR__ . '/../../../app/app.php';
    private const FIXTURES = __DIR__ . '/../../fixtures/app';

    /** Variables passed through from the parent process: PHP and Windows need them to start at all. */
    private const INHERITED_ENV = ['PATH', 'Path', 'SYSTEMROOT', 'SystemRoot', 'WINDIR', 'TEMP', 'TMP', 'TMPDIR'];

    private function __construct(
        public readonly string $root,
    ) {}

    /**
     * @param bool $withFixtures Copy the fixture classes and configs into `app/`, or start with an empty application.
     */
    public static function create(bool $withFixtures = true): self
    {
        $root = \sys_get_temp_dir() . '/temporal-bridge-acceptance-' . \bin2hex(\random_bytes(6));
        \mkdir($root . '/app/src', 0o777, true);
        \mkdir($root . '/app/config', 0o777, true);
        \mkdir($root . '/runtime', 0o777, true);

        $withFixtures and self::copyDirectory(self::FIXTURES, $root . '/app');

        return new self(\str_replace('\\', '/', $root));
    }

    /**
     * Runs a console command of the application.
     *
     * @param list<string> $arguments Command name and its arguments.
     * @param array<non-empty-string, string> $env Extra environment variables.
     * @param string|null $input Text for STDIN; STDIN is empty when null.
     */
    public function console(array $arguments, array $env = [], ?string $input = null): Result
    {
        return $this->exec([\PHP_BINARY, self::ENTRY_POINT, ...$arguments], $env, $input);
    }

    /**
     * Checks the syntax of a PHP file of the application with `php -l`.
     */
    public function lint(string $path): Result
    {
        return $this->exec([\PHP_BINARY, '-l', $this->path($path)]);
    }

    public function path(string $relative): string
    {
        return $this->root . '/' . \ltrim($relative, '/');
    }

    public function exists(string $relative): bool
    {
        return \is_file($this->path($relative));
    }

    public function read(string $relative): string
    {
        $content = \file_get_contents($this->path($relative));
        \is_string($content) or throw new \RuntimeException("Unable to read `$relative`.");

        return $content;
    }

    public function remove(string $relative): void
    {
        \unlink($this->path($relative));
    }

    /**
     * @return list<string> Paths of all files under `app/src`, relative to the root, sorted.
     */
    public function sourceFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->path('app/src'), \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            \assert($file instanceof \SplFileInfo);
            $files[] = \substr(\str_replace('\\', '/', $file->getPathname()), \strlen($this->root) + 1);
        }

        \sort($files);
        return $files;
    }

    public function destroy(): void
    {
        self::removeDirectory($this->root);
    }

    private static function copyDirectory(string $from, string $to): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );
        foreach ($iterator as $item) {
            \assert($item instanceof \SplFileInfo);
            $target = $to . '/' . \substr($item->getPathname(), \strlen($from) + 1);
            $item->isDir()
                ? (\is_dir($target) or \mkdir($target, 0o777, true))
                : \copy($item->getPathname(), $target);
        }
    }

    private static function removeDirectory(string $path): void
    {
        if (!\is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $item) {
            \assert($item instanceof \SplFileInfo);
            $item->isDir() ? \rmdir($item->getPathname()) : \unlink($item->getPathname());
        }

        \rmdir($path);
    }

    /**
     * @param non-empty-list<string> $command
     * @param array<non-empty-string, string> $env
     */
    private function exec(array $command, array $env = [], ?string $input = null): Result
    {
        $baseEnv = [];
        foreach (self::INHERITED_ENV as $name) {
            $value = \getenv($name);
            $value === false or $baseEnv[$name] = $value;
        }

        // Without a debugger or coverage driver: the child is a black box and must not slow the run down.
        $baseEnv['XDEBUG_MODE'] = 'off';

        // Output goes to files: a full pipe blocks the child on Windows when nobody drains it in time.
        $stdout = $this->path('runtime/stdout.log');
        $stderr = $this->path('runtime/stderr.log');

        $process = \proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['file', $stdout, 'w'], 2 => ['file', $stderr, 'w']],
            $pipes,
            $this->root,
            $env + $baseEnv,
        );
        \is_resource($process) or throw new \RuntimeException('Unable to start the process.');

        $input === null or \fwrite($pipes[0], $input);
        \fclose($pipes[0]);
        $exitCode = \proc_close($process);

        return new Result(
            exitCode: $exitCode,
            stdout: (string) \file_get_contents($stdout),
            stderr: (string) \file_get_contents($stderr),
        );
    }
}
