<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\Attributes\AttributeReader;
use Spiral\TemporalBridge\Declaration\DeclarationDto;
use Spiral\TemporalBridge\Declaration\DeclarationType;
use Spiral\TemporalBridge\DeclarationLocator;
use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class DeclarationLocatorTest extends TestCase
{
    private DeclarationLocator $locator;

    public function testEnumClassesShouldBeSkipped(): void
    {
        $this->locator->listen(new \ReflectionClass(TestEnum::class));
        $this->locator->listen(new \ReflectionClass(TestAbstractClass::class));
        $this->locator->listen(new \ReflectionClass(TestInterface::class));

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        Assert::count($result, 0);
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

        Assert::count($result, 4);

        Assert::same($result[0][0], WorkflowInterface::class);
        Assert::same($result[0][1], $workflow1);

        Assert::same($result[1][0], WorkflowInterface::class);
        Assert::same($result[1][1], $workflow2);

        Assert::same($result[2][0], ActivityInterface::class);
        Assert::same($result[2][1], $activity1);

        Assert::same($result[3][0], ActivityInterface::class);
        Assert::same($result[3][1], $activity2);
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

        Assert::count($result, 0);
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

        Assert::count($result, 4);

        Assert::same($result[0][0], WorkflowInterface::class);
        Assert::same($result[0][1], $workflow1);

        Assert::same($result[1][0], WorkflowInterface::class);
        Assert::same($result[1][1], $workflow2);

        Assert::same($result[2][0], ActivityInterface::class);
        Assert::same($result[2][1], $activity1);

        Assert::same($result[3][0], ActivityInterface::class);
        Assert::same($result[3][1], $activity2);
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

        Assert::count($result, 4);

        Assert::same($result[0][0], WorkflowInterface::class);
        Assert::same($result[0][1]->getName(), TestWorkflowClass::class);

        Assert::same($result[1][0], WorkflowInterface::class);
        Assert::same($result[1][1]->getName(), TestWorkflowClassWithInterface::class);

        Assert::same($result[2][0], ActivityInterface::class);
        Assert::same($result[2][1]->getName(), TestActivityClass::class);

        Assert::same($result[3][0], ActivityInterface::class);
        Assert::same($result[3][1]->getName(), TestActivityClassWithInterface::class);
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

        Assert::count($result, 4);

        Assert::same($result[0][0], WorkflowInterface::class);
        Assert::same($result[0][1]->getName(), TestWorkflowClass::class);

        Assert::same($result[1][0], WorkflowInterface::class);
        Assert::same($result[1][1]->getName(), TestWorkflowClassWithInterface::class);

        Assert::same($result[2][0], ActivityInterface::class);
        Assert::same($result[2][1]->getName(), TestActivityClass::class);

        Assert::same($result[3][0], ActivityInterface::class);
        Assert::same($result[3][1]->getName(), TestActivityClassWithInterface::class);
    }

    public function testGetDeclarationList(): void
    {
        $this->locator->addDeclaration($dto = new DeclarationDto(
            type: DeclarationType::Activity,
            class: new \ReflectionClass(TestActivityClass::class),
            taskQueue: 'custom',
        ));
        $this->locator->listen($workflow = new \ReflectionClass(TestWorkflowClass::class));

        $result = [...$this->locator->getDeclarationList()];

        Assert::count($result, 2);
        Assert::same($result[0], $dto);
        Assert::same($result[1]->type, DeclarationType::Workflow);
        Assert::same($result[1]->class, $workflow);
        Assert::null($result[1]->taskQueue);
    }

    public function testDeclarationsInheritedFromParentClassShouldBeRegistered(): void
    {
        $this->locator->listen($workflow = new \ReflectionClass(TestWorkflowClassWithParent::class));
        $this->locator->listen($activity = new \ReflectionClass(TestActivityClassWithParent::class));

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        Assert::count($result, 2);

        Assert::same($result[0][0], WorkflowInterface::class);
        Assert::same($result[0][1], $workflow);

        Assert::same($result[1][0], ActivityInterface::class);
        Assert::same($result[1][1], $activity);
    }

    public function testWrongClasses(): void
    {
        $this->locator->listen(new \ReflectionClass(\stdClass::class));

        $result = [];

        foreach ($this->locator->getDeclarations() as $type => $class) {
            $result[] = [$type, $class];
        }

        Assert::count($result, 0);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->locator = new DeclarationLocator(new AttributeReader());
    }
}

enum TestEnum {}

interface TestInterface {}

abstract class TestAbstractClass {}

#[WorkflowInterface]
class TestWorkflowClass {}

#[ActivityInterface]
class TestActivityClass {}

#[ActivityInterface]
interface TestActivityInterface {}

class TestActivityClassWithInterface implements TestActivityInterface {}

#[WorkflowInterface]
interface TestWorkflowInterface {}

class TestWorkflowClassWithInterface implements TestWorkflowInterface {}

#[WorkflowInterface]
abstract class TestAbstractWorkflowClass {}

class TestWorkflowClassWithParent extends TestAbstractWorkflowClass {}

#[ActivityInterface]
abstract class TestAbstractActivityClass {}

class TestActivityClassWithParent extends TestAbstractActivityClass {}
