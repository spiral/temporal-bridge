<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App;

use Temporal\Interceptor\ActivityInbound\ActivityInput;
use Temporal\Interceptor\ActivityInboundInterceptor;

final class SomeInterceptor implements ActivityInboundInterceptor
{
    public function handleActivityInbound(ActivityInput $input, callable $next): mixed
    {
    }
}
