<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App;

use Spiral\TemporalBridge\Attribute\AssignWorker;

#[AssignWorker(taskQueue: 'worker1')]
class SomeActivity
{
}
