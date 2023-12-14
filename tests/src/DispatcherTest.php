<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\RoadRunnerBridge\RoadRunnerMode;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Dispatcher;

final class DispatcherTest extends TestCase
{
    private \ReflectionMethod $method;
    private Dispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = new Dispatcher(
            RoadRunnerMode::Temporal,
            new AttributeReader(),
            new TemporalConfig(['defaultWorker' => 'foo']),
            $this->getContainer(),
        );

        $ref = new \ReflectionClass($this->dispatcher);
        $this->method = $ref->getMethod('resolveQueueName');
        $this->method->setAccessible(true);
    }

    public function testResolvingQueueNameWithAttributeOnClass(): void
    {
        $queue = $this->method->invoke(
            $this->dispatcher,
            new \ReflectionClass(ActivityInterfaceWithAttribute::class),
        );

        $this->assertSame('worker1', $queue);
    }

    public function testResolvingQueueNameWithAttributeOnParentClass(): void
    {
        $queue = $this->method->invoke(
            $this->dispatcher,
            new \ReflectionClass(ActivityClass::class),
        );

        $this->assertSame('worker1', $queue);
    }

    public function testResolvingQueueNameWithoutAttribute(): void
    {
        $queue = $this->method->invoke(
            $this->dispatcher,
            new \ReflectionClass(ActivityInterfaceWithoutAttribute::class),
        );

        $this->assertNull($queue);
    }
}

#[AssignWorker(name: 'worker1')]
interface ActivityInterfaceWithAttribute
{
}


interface ActivityInterfaceWithoutAttribute
{
}

class ActivityClass implements ActivityInterfaceWithAttribute
{
}
