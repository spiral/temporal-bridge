<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Nette\PhpGenerator\Method;
use Spiral\TemporalBridge\Generator\ActivityInterfaceGenerator;

final class ActivityInterfaceGeneratorTest extends TestCase
{
    public function testGenerateActivity(): void
    {
        $generator = new ActivityInterfaceGenerator();

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("#[ActivityInterface(prefix: 'Foo.')]\ninterface Foo")
            ->assertCodeContains("#[ActivityMethod]\n    public function handle(): mixed;");
    }

    public function testGenerateActivityWithHandlerMethod(): void
    {
        $generator = new ActivityInterfaceGenerator();

        $this->context->withHandlerMethod('foo');

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("#[ActivityInterface(prefix: 'Foo.')]\ninterface Foo")
            ->assertCodeContains("#[ActivityMethod]\n    public function foo(): mixed;");
    }

    public function testGenerateActivityWithActivityMethods(): void
    {
        $generator = new ActivityInterfaceGenerator();

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
            ->assertCodeContains("#[ActivityInterface(prefix: 'Foo.')]\ninterface Foo")
            ->assertCodeContains("#[ActivityMethod]\n    public function foo(\$baz);")
            ->assertCodeContains("#[ActivityMethod]\n    public function bar(\$foo = 'bar')");
    }
}
