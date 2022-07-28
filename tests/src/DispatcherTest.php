<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\Dispatcher;

final class DispatcherTest extends TestCase
{
    public function testResolvingQueueName(): void
    {
        $dispatcher = new Dispatcher(
            $this->createMock(EnvironmentInterface::class),
            new AttributeReader(),
            new TemporalConfig(['defaultWorker' => 'foo']),
            $this->getContainer(),
        );

        $ref = new \ReflectionClass($dispatcher);
        $method = $ref->getMethod('resolveQueueName');
        $method->setAccessible(true);

        $queue = $method->invoke(
            $dispatcher,
            new \ReflectionClass(ActivityInterfaceWithAttribute::class)
        );
        $this->assertSame('worker1', $queue);

        $queue = $method->invoke(
            $dispatcher,
            new \ReflectionClass(ActivityInterfaceWithoutAttribute::class)
        );
        $this->assertSame('foo', $queue);
    }
}

#[AssignWorker(name: 'worker1')]
interface ActivityInterfaceWithAttribute
{
}


interface ActivityInterfaceWithoutAttribute
{
}
