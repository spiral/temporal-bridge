<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Spiral\Files\FilesInterface;

/**
 * @internal
 */
class PhpCodePrinter
{
    public function __construct(
        private readonly PhpNamespace $namespace,
        private readonly Context $context
    ) {
    }

    public function print(FilesInterface $files): void
    {
        $file = new PhpFile;
        $file->addNamespace($this->namespace);
        $file->setStrictTypes();

        $printer = new PsrPrinter;

        $files->write(
            filename: $this->context->getClassPath(),
            data: $printer->printFile($file),
            ensureDirectory: true
        );
    }
}
