<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App;

use Spiral\TemporalBridge\Attribute\AssignWorker;

#[AssignWorker(name: 'worker2')]
class SomeWorkflow
{
}
