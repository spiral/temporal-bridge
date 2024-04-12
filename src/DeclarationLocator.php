<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

#[Singleton]
#[TargetAttribute(WorkflowInterface::class)]
#[TargetAttribute(ActivityInterface::class)]
final class DeclarationLocator implements DeclarationLocatorInterface, TokenizationListenerInterface
{
    private array $declarations = [];

    public function __construct(
        private readonly ReaderInterface $reader,
    ) {
    }

    public function getDeclarations(): iterable
    {
        foreach ($this->declarations as $type => $classes) {
            foreach ($classes as $class) {
                yield $type => $class;
            }
        }
    }

    public function listen(\ReflectionClass $class): void
    {
        if ($class->isAbstract() || $class->isInterface() || $class->isEnum()) {
            return;
        }

        foreach (\array_merge($class->getInterfaces(), [$class]) as $type) {
            if ($this->reader->firstClassMetadata($type, WorkflowInterface::class) !== null) {
                $this->declarations[WorkflowInterface::class][] = $class;
            } elseif ($this->reader->firstClassMetadata($type, ActivityInterface::class) !== null) {
                $this->declarations[ActivityInterface::class][] = $class;
            }
        }
    }

    public function finalize(): void
    {
        // do nothing
    }
}
