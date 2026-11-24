<?php

declare(strict_types=1);

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Bridge\Mockery\MockeryPlugin;

\ini_set('error_reporting', '-1');
\ini_set('memory_limit', '-1');

return new ApplicationConfig(
    src: ['src'],
    suites: [
        new SuiteConfig(
            name: 'Unit',
            location: ['tests/src'],
        ),
        new SuiteConfig(
            name: 'Acceptance',
            location: ['tests/acceptance/src'],
        ),
    ],
    plugins: [new MockeryPlugin()],
);
