<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Testing;

use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Sets an environment variable of the application booted for the test.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
#[FallbackInterceptor(EnvInterceptor::class)]
final readonly class Env implements Interceptable
{
    public function __construct(
        public string $key,
        public int|string|bool|null $value = null,
    ) {}
}
