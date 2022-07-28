<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Spiral\TemporalBridge\Generator\HandlerGenerator;

final class HandlerGeneratorTest extends TestCase
{
    public function testGenerateActivity(): void
    {
        $generator = new HandlerGenerator();

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("class Foo implements FooInterface")
            ->assertCodeContains("public function handle(): RunningWorkflow")
            ->assertCodeContains("->create(FooWorkflowInterface::class)")
            ->assertCodeContains("\$run = \$workflow->run();");
    }
}
