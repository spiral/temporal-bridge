<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Mockery as m;
use Spiral\Attributes\AttributeReader;
use Spiral\TemporalBridge\DeclarationLocator;
use Spiral\Tokenizer\ClassesInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

final class DeclarationLocatorTest extends TestCase
{
    private DeclarationLocator $locator;
    private m\LegacyMockInterface|m\MockInterface $classes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new DeclarationLocator(
            $this->classes = m::mock(ClassesInterface::class),
            new AttributeReader()
        );
    }

    public function testEnumClassesShouldBeSkipped(): void
    {
        $this->classes->shouldReceive('getClasses')->once()->andReturn([
            new \ReflectionClass(TestEnum::class),
            new \ReflectionClass(TestAbstractClass::class),
            new \ReflectionClass(TestInterface::class),
        ]);

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        $this->assertCount(0, $result);
    }

    public function testWorkflowsShouldBeRegistered(): void
    {
        $this->classes->shouldReceive('getClasses')->once()->andReturn([
            new \ReflectionClass(TestEnum::class),
            new \ReflectionClass(TestAbstractClass::class),
            new \ReflectionClass(TestInterface::class),
            $workflow1 = new \ReflectionClass(TestWorkflowClass::class),
            $workflow2 = new \ReflectionClass(TestWorkflowClassWithInterface::class),
            $activity1 = new \ReflectionClass(TestActivityClass::class),
            $activity2 = new \ReflectionClass(TestActivityClassWithInterface::class),
        ]);

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        $this->assertCount(4, $result);

        $this->assertSame(WorkflowInterface::class, $result[0][0]);
        $this->assertSame($workflow1, $result[0][1]);

        $this->assertSame(WorkflowInterface::class, $result[1][0]);
        $this->assertSame($workflow2, $result[1][1]);

        $this->assertSame(ActivityInterface::class, $result[2][0]);
        $this->assertSame($activity1, $result[2][1]);

        $this->assertSame(ActivityInterface::class, $result[3][0]);
        $this->assertSame($activity2, $result[3][1]);
    }
}

enum TestEnum
{
}

interface TestInterface
{

}

abstract class TestAbstractClass
{

}

#[WorkflowInterface]
class TestWorkflowClass
{

}

#[ActivityInterface]
class TestActivityClass
{

}

#[ActivityInterface]
interface TestActivityInterface
{

}

class TestActivityClassWithInterface implements TestActivityInterface
{

}

#[WorkflowInterface]
interface TestWorkflowInterface
{

}

class TestWorkflowClassWithInterface implements TestWorkflowInterface
{

}
