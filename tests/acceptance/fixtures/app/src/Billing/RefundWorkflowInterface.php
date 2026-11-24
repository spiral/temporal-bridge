<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App\Billing;

use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface RefundWorkflowInterface
{
    #[WorkflowMethod]
    public function refund(): void;
}
