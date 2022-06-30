<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\App;

use Spiral\TemporalBridge\Attribute\RegisterWorker;

#[RegisterWorker(name: 'worker2')]
class SomeWorkflow
{
}
