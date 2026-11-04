<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Testing;

use Spiral\TemporalBridge\Tests\TestCase;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;
use Testo\Pipeline\Policy\ConflictPolicy;

/**
 * Hands the {@see Env} value to the test case before its `#[BeforeTest]` hooks boot the application:
 * the lifecycle interceptor runs closer to the test than the default order used here.
 *
 * @internal
 */
#[InterceptorOptions(onConflict: ConflictPolicy::Merge)]
final readonly class EnvInterceptor implements TestRunInterceptor
{
    public function __construct(
        private Env $env,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $instance = $info->caseInfo->instance?->getInstance();
        $instance instanceof TestCase and $instance->env[$this->env->key] = $this->env->value;

        return $next($info);
    }
}
