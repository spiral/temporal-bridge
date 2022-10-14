<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Nette\PhpGenerator\Method;
use Spiral\TemporalBridge\Generator\WorkflowGenerator;

final class WorkflowGeneratorTest extends TestCase
{
    public function testGenerateWorkflow(): void
    {
        $generator = new WorkflowGenerator();

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("class Foo implements FooInterface")
            ->assertCodeContains('private ActivityProxy $activity;')
            ->assertCodeContains('$this->activity = Workflow::newActivityStub')
            ->assertCodeContains("public function handle(): mixed")
            ->assertCodeContains('yield $this->activity->handle();')
            ->assertCodeNotContains("#[AssignWorker(name: 'foo')]");
    }

    public function testGenerateWorkflowWithTaskQueue(): void
    {
        $generator = new WorkflowGenerator();

        $this->context->withTaskQueue('foo');

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("use Spiral\TemporalBridge\Attribute\AssignWorker;")
            ->assertCodeContains("#[AssignWorker(name: 'foo')]\nclass Foo implements FooInterface")
            ->assertCodeContains('private ActivityProxy $activity;')
            ->assertCodeContains('$this->activity = Workflow::newActivityStub')
            ->assertCodeContains("->withTaskQueue('foo')")
            ->assertCodeContains("public function handle(): mixed")
            ->assertCodeContains('yield $this->activity->handle();');
    }

    public function testGenerateWorkflowWithActivityMethods(): void
    {
        $generator = new WorkflowGenerator();

        $method1 = new Method('foo');
        $method1->setPublic();
        $method1->addParameter('baz');

        $method2 = new Method('bar');
        $method2->setPublic();
        $method2->addParameter('foo', 'bar');

        $this->context->withActivityMethods([$method1, $method2]);

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("class Foo implements FooInterface")
            ->assertCodeContains('private ActivityProxy $activity;')
            ->assertCodeContains('$this->activity = Workflow::newActivityStub')
            ->assertCodeContains("public function handle(): mixed")
            ->assertCodeContains('yield $this->activity->foo($baz);')
            ->assertCodeContains('yield $this->activity->bar($foo);')
            ->assertCodeNotContains("#[AssignWorker(name: 'foo')]");
    }

    public function testGenerateWorkflowWithSignalMethods(): void
    {
        $generator = new WorkflowGenerator();

        $method1 = new Method('foo');
        $method1->setPublic();
        $method1->addParameter('baz');

        $method2 = new Method('bar');
        $method2->setPublic();
        $method2->addParameter('foo', 'bar');

        $this->context->withSignalMethods([$method1, $method2]);

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("class Foo implements FooInterface")
            ->assertCodeContains('private ActivityProxy $activity;')
            ->assertCodeContains('$this->activity = Workflow::newActivityStub')
            ->assertCodeContains("public function handle(): mixed")
            ->assertCodeContains('yield $this->activity->handle();')
            ->assertCodeContains('public function foo($baz)')
            ->assertCodeContains('// Signal about something special.')
            ->assertCodeContains('public function bar($foo = \'bar\')')
            ->assertCodeNotContains("#[AssignWorker(name: 'foo')]");
    }

    public function testGenerateWorkflowWithQueryMethods(): void
    {
        $generator = new WorkflowGenerator();

        $method1 = new Method('foo');
        $method1->setPublic();
        $method1->addParameter('baz');

        $method2 = new Method('bar');
        $method2->setPublic();
        $method2->addParameter('foo', 'bar');

        $this->context->withQueryMethods([$method1, $method2]);

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("class Foo implements FooInterface")
            ->assertCodeContains('private ActivityProxy $activity;')
            ->assertCodeContains('$this->activity = Workflow::newActivityStub')
            ->assertCodeContains("public function handle(): mixed")
            ->assertCodeContains('yield $this->activity->handle();')
            ->assertCodeContains('public function foo($baz)')
            ->assertCodeContains('// Query something special.')
            ->assertCodeContains('public function bar($foo = \'bar\')')
            ->assertCodeNotContains("#[AssignWorker(name: 'foo')]");
    }
}
