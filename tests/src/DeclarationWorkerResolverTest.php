<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Config\TemporalConfig;
use Spiral\TemporalBridge\DeclarationWorkerResolver;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class DeclarationWorkerResolverTest extends TestCase
{
    private DeclarationWorkerResolver $resolver;

    public function testResolvingQueueNameWithAttributeOnClass(): void
    {
        $queue = $this->resolver->resolve(
            new \ReflectionClass(ActivityInterfaceWithAttribute::class),
        );

        Assert::same($queue, ['worker1']);
    }

    public function testResolvingQueueNameWithMultipleAttributeOnClass(): void
    {
        $queue = $this->resolver->resolve(
            new \ReflectionClass(ActivityInterfaceWithMultipleAttributes::class),
        );

        Assert::same($queue, ['worker1', 'worker2']);
    }

    public function testResolvingQueueNameWithAttributeOnParentClass(): void
    {
        $queue = $this->resolver->resolve(
            new \ReflectionClass(ActivityClass::class),
        );

        Assert::same($queue, ['worker1']);
    }

    public function testResolvingQueueNameWithoutAttribute(): void
    {
        $queue = $this->resolver->resolve(
            new \ReflectionClass(ActivityInterfaceWithoutAttribute::class),
        );

        Assert::same($queue, ['foo']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->resolver = new DeclarationWorkerResolver(
            new AttributeReader(),
            new TemporalConfig(['defaultWorker' => 'foo']),
        );
    }
}

#[AssignWorker(taskQueue: 'worker1')]
interface ActivityInterfaceWithAttribute {}

#[AssignWorker(taskQueue: 'worker1')]
#[AssignWorker(taskQueue: 'worker2')]
interface ActivityInterfaceWithMultipleAttributes {}


interface ActivityInterfaceWithoutAttribute {}

class ActivityClass implements ActivityInterfaceWithAttribute {}
