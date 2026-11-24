<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App\Billing;

/**
 * Has no Temporal attributes, so it must not be listed.
 */
final class NotARegisteredClass
{
    public function handle(): void {}
}
