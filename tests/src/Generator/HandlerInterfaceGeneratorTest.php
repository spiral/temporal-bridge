<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Spiral\TemporalBridge\Generator\HandlerInterfaceGenerator;

final class HandlerInterfaceGeneratorTest extends TestCase
{
    public function testGenerateActivity(): void
    {
        $generator = new HandlerInterfaceGenerator();

        $this->generate($generator)
            ->assertFilename('app/src/Foo.php')
            ->assertCodeContains("namespace App\Workflow")
            ->assertCodeContains("interface Foo")
            ->assertCodeContains("public function handle(): RunningWorkflow;");
    }
}
