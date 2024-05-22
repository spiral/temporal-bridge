<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\TemporalBridge\Declaration\DeclarationDto;
use Spiral\TemporalBridge\Declaration\DeclarationType;
use Spiral\TemporalBridge\DeclarationLocator;
use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

final class DeclarationLocatorTest extends TestCase
{
    private DeclarationLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new DeclarationLocator(new AttributeReader());
    }

    public function testEnumClassesShouldBeSkipped(): void
    {
        $this->locator->listen(new \ReflectionClass(TestEnum::class));
        $this->locator->listen(new \ReflectionClass(TestAbstractClass::class));
        $this->locator->listen(new \ReflectionClass(TestInterface::class));

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        $this->assertCount(0, $result);
    }

    public function testWorkflowsShouldBeRegistered(): void
    {
        $this->locator->listen(new \ReflectionClass(TestEnum::class));
        $this->locator->listen(new \ReflectionClass(TestAbstractClass::class));
        $this->locator->listen(new \ReflectionClass(TestInterface::class));
        $this->locator->listen($workflow1 = new \ReflectionClass(TestWorkflowClass::class));
        $this->locator->listen($workflow2 = new \ReflectionClass(TestWorkflowClassWithInterface::class));
        $this->locator->listen($activity1 = new \ReflectionClass(TestActivityClass::class));
        $this->locator->listen($activity2 = new \ReflectionClass(TestActivityClassWithInterface::class));

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

    public function testAddDeclarationSkipsNonClasses(): void
    {
        $this->locator->addDeclaration(TestEnum::class);
        $this->locator->addDeclaration(TestAbstractClass::class);
        $this->locator->addDeclaration(TestInterface::class);
        $this->locator->addDeclaration(new \ReflectionClass(TestEnum::class));
        $this->locator->addDeclaration(new \ReflectionClass(TestAbstractClass::class));
        $this->locator->addDeclaration(new \ReflectionClass(TestInterface::class));

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        $this->assertCount(0, $result);
    }

    public function testAddDeclarationReflections(): void
    {
        $this->locator->addDeclaration($workflow1 = new \ReflectionClass(TestWorkflowClass::class));
        $this->locator->addDeclaration($workflow2 = new \ReflectionClass(TestWorkflowClassWithInterface::class));
        $this->locator->addDeclaration($activity1 = new \ReflectionClass(TestActivityClass::class));
        $this->locator->addDeclaration($activity2 = new \ReflectionClass(TestActivityClassWithInterface::class));

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

    public function testAddDeclarationClassNames(): void
    {
        $this->locator->addDeclaration(TestWorkflowClass::class);
        $this->locator->addDeclaration(TestWorkflowClassWithInterface::class);
        $this->locator->addDeclaration(TestActivityClass::class);
        $this->locator->addDeclaration(TestActivityClassWithInterface::class);

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        $this->assertCount(4, $result);

        $this->assertSame(WorkflowInterface::class, $result[0][0]);
        $this->assertSame(TestWorkflowClass::class, $result[0][1]->getName());

        $this->assertSame(WorkflowInterface::class, $result[1][0]);
        $this->assertSame(TestWorkflowClassWithInterface::class, $result[1][1]->getName());

        $this->assertSame(ActivityInterface::class, $result[2][0]);
        $this->assertSame(TestActivityClass::class, $result[2][1]->getName());

        $this->assertSame(ActivityInterface::class, $result[3][0]);
        $this->assertSame(TestActivityClassWithInterface::class, $result[3][1]->getName());
    }

    public function testAddDeclarationDto(): void
    {
        $this->locator->addDeclaration(new DeclarationDto(
            type: DeclarationType::Workflow,
            class: new \ReflectionClass(TestWorkflowClass::class),
        ));
        $this->locator->addDeclaration(new DeclarationDto(
            type: DeclarationType::Workflow,
            class: new \ReflectionClass(TestWorkflowClassWithInterface::class),
        ));
        $this->locator->addDeclaration(new DeclarationDto(
            type: DeclarationType::Activity,
            class: new \ReflectionClass(TestActivityClass::class),
        ));
        $this->locator->addDeclaration(new DeclarationDto(
            type: DeclarationType::Activity,
            class: new \ReflectionClass(TestActivityClassWithInterface::class),
        ));

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        $this->assertCount(4, $result);

        $this->assertSame(WorkflowInterface::class, $result[0][0]);
        $this->assertSame(TestWorkflowClass::class, $result[0][1]->getName());

        $this->assertSame(WorkflowInterface::class, $result[1][0]);
        $this->assertSame(TestWorkflowClassWithInterface::class, $result[1][1]->getName());

        $this->assertSame(ActivityInterface::class, $result[2][0]);
        $this->assertSame(TestActivityClass::class, $result[2][1]->getName());

        $this->assertSame(ActivityInterface::class, $result[3][0]);
        $this->assertSame(TestActivityClassWithInterface::class, $result[3][1]->getName());
    }

    public function testWrongClasses(): void
    {
        $this->locator->listen(new \ReflectionClass(\stdClass::class));

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        $this->assertCount(0, $result);
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
