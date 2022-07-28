<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Nette\PhpGenerator\Method;
use Spiral\TemporalBridge\Generator\WorkflowInterfaceGenerator;

final class WorkflowInterfaceGeneratorTest extends TestCase
{
    public function testGenerateWorkflow(): void
    {
        $generator = new WorkflowInterfaceGenerator();

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("#[WorkflowInterface]\ninterface Foo")
            ->assertCodeContains("#[WorkflowMethod]\n    public function handle(): mixed;");
    }

    public function testGenerateWorkflowWithSignalMethods(): void
    {
        $generator = new WorkflowInterfaceGenerator();

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
            ->assertCodeContains("#[WorkflowInterface]\ninterface Foo")
            ->assertCodeContains("#[WorkflowMethod]\n    public function handle(): mixed;")
            ->assertCodeContains("#[SignalMethod]\n    public function foo(\$baz);")
            ->assertCodeContains("#[SignalMethod]\n    public function bar(\$foo = 'bar');");
    }

    public function testGenerateWorkflowWithQueryMethods(): void
    {
        $generator = new WorkflowInterfaceGenerator();

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
            ->assertCodeContains("#[WorkflowInterface]\ninterface Foo")
            ->assertCodeContains("#[WorkflowMethod]\n    public function handle(): mixed;")
            ->assertCodeContains("#[QueryMethod]\n    public function foo(\$baz);")
            ->assertCodeContains("#[QueryMethod]\n    public function bar(\$foo = 'bar');");
    }
}
