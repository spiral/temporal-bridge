<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\ClassesInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

final class DeclarationLocator implements DeclarationLocatorInterface
{
    public function __construct(
        private ClassesInterface $classes,
        private ReaderInterface $reader
    ) {
    }

    public function getDeclarations(): iterable
    {
        foreach ($this->classes->getClasses() as $class) {
            if ($class->isAbstract() || $class->isInterface()) {
                continue;
            }

            foreach (array_merge($class->getInterfaces(), [$class]) as $type) {
                if ($this->reader->firstClassMetadata($type, WorkflowInterface::class)) {
                    yield WorkflowInterface::class => $class;
                } else if ($this->reader->firstClassMetadata($type, ActivityInterface::class)) {
                    yield ActivityInterface::class => $class;
                }
            }
        }
    }
}
