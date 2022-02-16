<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\PhpNamespace;
use Spiral\Files\FilesInterface;

final class Generator
{
    public function __construct(
        private FilesInterface $files
    ) {
    }

    public function generate(Context $context, array $generators): void
    {
        foreach ($generators as $name => $generator) {
            $generator->generate(
                $context->withClassNamePostfix($name),
                new PhpNamespace($context->getNamespace())
            )->print($this->files);
        }
    }
}
