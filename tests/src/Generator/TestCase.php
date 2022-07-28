<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Mockery as m;
use Nette\PhpGenerator\PhpNamespace;
use Spiral\Files\FilesInterface;
use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\FileGeneratorInterface;

abstract class TestCase extends \Spiral\TemporalBridge\Tests\TestCase
{
    protected const CONTEXT_BASE_CLASS = 'Foo';
    protected const CONTEXT_NAMESPACE = 'App\Workflow';
    protected Context $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new Context(
            directory: 'app/src/',
            namespace: self::CONTEXT_NAMESPACE,
            baseClass: self::CONTEXT_BASE_CLASS
        );

        $this->files = m::mock(FilesInterface::class);
    }

    protected function generate(FileGeneratorInterface $generator): GeneratorAssertions
    {
        $assertions = new GeneratorAssertions();

        $this->files->shouldReceive('write')->once()->withArgs(
            function (string $filename, string $data) use ($assertions) {
                $assertions->withFilename($filename)->withData($data);

                return true;
            }
        );

        $generator
            ->generate($this->context, new PhpNamespace($this->context->getNamespace()))
            ->print($this->files);

        return $assertions;
    }
}
