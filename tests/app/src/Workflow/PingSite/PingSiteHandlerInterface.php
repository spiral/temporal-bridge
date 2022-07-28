<?php

declare(strict_types=1);

namespace App\Workflow\PingSite;

use Spiral\TemporalBridge\Workflow\RunningWorkflow;

interface PingSiteHandlerInterface
{
    public function handle(string $name): RunningWorkflow;
}
