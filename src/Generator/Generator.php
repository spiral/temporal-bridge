<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\PhpNamespace;
use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Generator
{
    public function __construct(
        private FilesInterface $files
    ) {
    }

    public function generate(
        OutputInterface $output,
        Context $context,
        array $generators,
    ): void {
        $output->writeln('<info>Generating workflow files...</info>');

        foreach ($generators as $name => $generator) {
            $generator->generate(
                $context = $context->withClassPostfix($name),
                new PhpNamespace($context->getNamespace())
            )->print($this->files);

            $output->writeln(\sprintf(
                '<info>Class [%s] successfully generated.</info>',
                $context->getClassWithNamespace()
            ));
        }
    }
}
