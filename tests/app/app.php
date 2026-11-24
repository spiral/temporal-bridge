<?php

declare(strict_types=1);

/**
 * Console entry point of the test application, run as a subprocess by the Acceptance suite.
 *
 * The working directory is the application root: classes live in `app/src`, configs in `app/config`.
 */

use Spiral\TemporalBridge\Tests\App\TestApp;

require __DIR__ . '/../../vendor/autoload.php';

$root = \getcwd();

// Composer maps the kernel namespace to tests/app/src, while discovered and generated classes live under the root.
\spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'Spiral\\TemporalBridge\\Tests\\App\\';
    if (!\str_starts_with($class, $prefix)) {
        return;
    }

    $file = $root . '/app/src/' . \str_replace('\\', '/', \substr($class, \strlen($prefix))) . '.php';
    \is_file($file) and require $file;
});

$app = TestApp::create(directories: ['root' => $root])->withBootloaders([
    \Spiral\Boot\Bootloader\ConfigurationBootloader::class,
    \Spiral\Bootloader\Attributes\AttributesBootloader::class,
    \Spiral\Console\Bootloader\ConsoleBootloader::class,
    \Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader::class,
]);

$app->run() === null and exit(255);

exit((int) $app->serve());
