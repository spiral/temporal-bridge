<?php

declare(strict_types=1);

namespace App\Workflow\PingSite;

use Temporal\Workflow\QueryMethod;
use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface PingSiteWorkflowInterface
{
    #[WorkflowMethod]
    public function handle(string $name): mixed;
}
