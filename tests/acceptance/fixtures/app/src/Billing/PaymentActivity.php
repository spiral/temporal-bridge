<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App\Billing;

use Spiral\TemporalBridge\Attribute\AssignWorker;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface(prefix: 'payment.')]
#[AssignWorker(taskQueue: 'payments')]
final class PaymentActivity
{
    #[ActivityMethod]
    public function charge(): void {}

    #[ActivityMethod(name: 'giveBack')]
    public function refund(): void {}
}
