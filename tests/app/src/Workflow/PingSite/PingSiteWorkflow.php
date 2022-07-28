<?php

declare(strict_types=1);

namespace App\Workflow\PingSite;

use Carbon\CarbonInterval;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Temporal\Activity\ActivityOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow;

class PingSiteWorkflow implements PingSiteWorkflowInterface
{
    /** @var ActivityProxy|PingSiteActivityInterface */
    private ActivityProxy $activity;

    public function __construct()
    {
        $this->activity = Workflow::newActivityStub(
            PingSiteActivityInterface::class,
            ActivityOptions::new()
                ->withScheduleToCloseTimeout(CarbonInterval::seconds(10))
        );
    }

    public function handle(string $name): mixed
    {
        return yield $this->activity->handle($name);
    }
}
