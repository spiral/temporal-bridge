<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationWorkerResolver;

final class DeclarationWorkerResolverTest extends TestCase
{
    private DeclarationWorkerResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new DeclarationWorkerResolver(
            new AttributeReader(),
            new TemporalConfig(['defaultWorker' => 'foo']),
        );
    }

    public function testResolvingQueueNameWithAttributeOnClass(): void
    {
        $queue = $this->resolver->resolve(
            new \ReflectionClass(ActivityInterfaceWithAttribute::class),
        );

        $this->assertSame('worker1', $queue);
    }

    public function testResolvingQueueNameWithAttributeOnParentClass(): void
    {
        $queue = $this->resolver->resolve(
            new \ReflectionClass(ActivityClass::class),
        );

        $this->assertSame('worker1', $queue);
    }

    public function testResolvingQueueNameWithoutAttribute(): void
    {
        $queue = $this->resolver->resolve(
            new \ReflectionClass(ActivityInterfaceWithoutAttribute::class),
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

class ActivityClass implements ActivityInterfaceWithAttribute
{
}
