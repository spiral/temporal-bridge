<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Spiral\TemporalBridge\Generator\ActivityGenerator;

final class ActivityGeneratorTest extends TestCase
{
    public function testGenerateActivity(): void
    {
        $generator = new ActivityGenerator();

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("class Foo implements FooInterface")
            ->assertCodeContains("public function handle(): mixed")
            ->assertCodeNotContains("#[AssignWorker(name: 'foo')]");
    }

    public function testGenerateActivityWithHandlerMethod(): void
    {
        $generator = new ActivityGenerator();

        $this->context->withHandlerMethod('foo');

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("class Foo implements FooInterface")
            ->assertCodeContains("public function foo(): mixed")
            ->assertCodeNotContains("#[AssignWorker(name: 'foo')]");
    }

    public function testGenerateActivityWithTaskQueue(): void
    {
        $generator = new ActivityGenerator();

        $this->context->withTaskQueue('foo');

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("#[AssignWorker(name: 'foo')]\nclass Foo implements FooInterface")
            ->assertCodeContains("public function handle(): mixed")
            ->assertCodeContains("use Spiral\TemporalBridge\Attribute\AssignWorker;");
    }
}
