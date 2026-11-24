<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App\Billing;

use Spiral\TemporalBridge\Attribute\AssignWorker;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
#[AssignWorker(taskQueue: 'billing')]
final class InvoiceWorkflow
{
    #[WorkflowMethod(name: 'billing.invoice')]
    public function handle(): void {}
}
