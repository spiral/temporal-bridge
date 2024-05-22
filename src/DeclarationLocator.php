<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\TemporalBridge\Declaration\DeclarationDto;
use Spiral\TemporalBridge\Declaration\DeclarationType;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Workflow\WorkflowInterface;

#[Singleton]
#[TargetAttribute(WorkflowInterface::class, scanParents: true)]
#[TargetAttribute(ActivityInterface::class, scanParents: true)]
final class DeclarationLocator implements
    DeclarationRegistryInterface,
    TokenizationListenerInterface,
    DeclarationLocatorInterface
{
    /** @var list<DeclarationDto> */
    private array $declarations = [];

    public function __construct(
        private readonly ReaderInterface $reader,
    ) {
    }

    public function addDeclaration(DeclarationDto|\ReflectionClass|string $class): void
    {
        if ($class instanceof DeclarationDto) {
            $this->declarations[] = $class;
            return;
        }

        $this->listen($class instanceof \ReflectionClass ? $class : new \ReflectionClass($class));
    }

    public function getDeclarationList(): iterable
    {
        return $this->declarations;
    }

    public function getDeclarations(): iterable
    {
        foreach ($this->declarations as $declaration) {
            yield match($declaration->type) {
                DeclarationType::Workflow => WorkflowInterface::class,
                DeclarationType::Activity => ActivityInterface::class,
            } => $declaration->class;
        }
    }

    public function listen(\ReflectionClass $class): void
    {
        if ($class->isAbstract() || $class->isInterface() || $class->isEnum()) {
            return;
        }

        /** @var DeclarationType|null $type */
        $type = null;

        foreach (\array_merge($class->getInterfaces(), [$class]) as $reflection) {
            if ($this->reader->firstClassMetadata($reflection, WorkflowInterface::class) !== null) {
                $type = DeclarationType::Workflow;
                break;
            }

            if ($this->reader->firstClassMetadata($reflection, ActivityInterface::class) !== null) {
                $type = DeclarationType::Activity;
                break;
            }
        }

        if ($type !== null) {
            $this->declarations[] = new DeclarationDto(
                type: $type,
                class: $class,
                taskQueue: null,
            );
        }

    }

    public function finalize(): void
    {
        // do nothing
    }
}
