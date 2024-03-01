<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App;

use Spiral\Core\Attribute\Scope;
use Spiral\TemporalBridge\Attribute\AssignWorker;

#[Scope('temporal.activity')]
#[AssignWorker(taskQueue: 'worker1')]
class SomeActivityWithScope
{
    // Binding ArrayAccess $tasks available only in temporal.activity scope
    public function __construct(
        private readonly \ArrayAccess $tasks,
    ) {
    }
}
