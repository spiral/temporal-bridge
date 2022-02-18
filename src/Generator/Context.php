<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

final class Context
{
    private const INTERFACE = 'Interface';

    private bool $withActivity = false;
    private bool $withHandler = false;
    private bool $scheduled = false;
    private string $handlerMethod = 'handle';
    private array $signalMethods = [];
    private array $queryMethods = [];
    private array $parameters = ['name' => 'string'];
    private string $classPostfix = '';

    public function __construct(
        private string $directory,
        private string $namespace,
        private string $baseClass,
    ) {
    }

    public function withClassPostfix(string $postfix): self
    {
        $self = clone $this;
        $self->classPostfix = $postfix;

        return $self;
    }

    public function withCronSchedule(): self
    {
        $self = clone $this;
        $self->scheduled = true;

        return $self;
    }

    public function withMethodParameters(array $parameters): self
    {
        $self = clone $this;
        $self->parameters = $parameters;

        return $self;
    }

    public function withSignalMethods(array $methods): self
    {
        $self = clone $this;
        $self->signalMethods = $methods;

        return $self;
    }

    public function withQueryMethods(array $methods): self
    {
        $self = clone $this;
        $self->queryMethods = $methods;

        return $self;
    }

    public function withNamespace(string $namespace): self
    {
        $self = clone $this;
        $self->namespace = $namespace;

        return $self;
    }

    public function withHandlerMethod(string $name): self
    {
        $self = clone $this;
        $self->handlerMethod = $name;

        return $self;
    }

    public function withActivity(): self
    {
        $self = clone $this;
        $self->withActivity = true;

        return $self;
    }

    public function withHandler(): self
    {
        $self = clone $this;
        $self->withHandler = true;

        return $self;
    }

    /**
     * Get the namespace
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Get base class name without the namespace
     */
    public function getBaseClass(string $postfix = ''): string
    {
        return $this->baseClass.$postfix;
    }

    /**
     * Get base class interface without the namespace
     */
    public function getBaseClassInterface(string $postfix = ''): string
    {
        return $this->getBaseClass($postfix).self::INTERFACE;
    }

    /**
     * Get base class interface with the namespace
     */
    public function getBaseClassInterfaceWithNamespace(string $postfix = ''): string
    {
        return $this->getNamespace().'\\'.$this->getBaseClassInterface($postfix);
    }

    /**
     * Get current class name without the namespace
     */
    public function getClass(string $postfix = ''): string
    {
        if ($this->classPostfix !== '' && str_ends_with($this->baseClass, $this->classPostfix)) {
            return $this->getBaseClass($postfix);
        }

        return $this->baseClass.$this->classPostfix.$postfix;
    }

    /**
     * Get current class name with the namespace
     */
    public function getClassWithNamespace(string $postfix = ''): string
    {
        return $this->getNamespace().'\\'.$this->getClass($postfix);
    }

    /**
     * Get current class interface without the namespace
     */
    public function getClassInterface(string $postfix = ''): string
    {
        return $this->getClass($postfix).self::INTERFACE;
    }

    /**
     * Get current class interface with the namespace
     */
    public function getClassInterfaceWithNamespace(string $postfix = ''): string
    {
        return $this->getNamespace().'\\'.$this->getClassInterface($postfix);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->directory;
    }

    /**
     * Get current class file full path
     */
    public function getClassPath(): string
    {
        return $this->directory.$this->getClass().'.php';
    }

    /**
     * Get required query methods
     * @return array<non-empty-string, non-empty-string>
     */
    public function getSignalMethods(): array
    {
        return $this->signalMethods;
    }

    /**
     * Get required query methods
     * @return string[]
     */
    public function getQueryMethods(): array
    {
        return $this->queryMethods;
    }

    /**
     * Check if workflow should be scheduled by cron expression
     */
    public function isScheduled(): bool
    {
        return $this->scheduled;
    }

    /**
     * Check if workflow should have activity classes
     */
    public function hasActivity(): bool
    {
        return $this->withActivity;
    }

    /**
     * Check if workflow should have handler classes
     */
    public function hasHandler(): bool
    {
        return $this->withHandler;
    }

    /**
     * Get workflow handler method name
     */
    public function getHandlerMethodName(): string
    {
        return $this->handlerMethod;
    }

    /**
     * Get workflow handler parameters
     * @return array|string[]
     */
    public function getHandlerParameters(): array
    {
        return $this->parameters;
    }
}
