<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

final class Context
{
    private bool $scheduled = false;
    private string $namespace;
    private string $baseClassName;
    private string $method = 'handle';
    private array $signalMethods = [];
    private array $queryMethods = [];
    private array $parameters = ['name' => 'string'];
    private string $directory;
    private string $postfix = '';

    public function withClassBaseName(string $name): self
    {
        $self = clone $this;
        $self->baseClassName = $name;

        return $self;
    }

    public function withClassNamePostfix(string $postfix): self
    {
        $self = clone $this;
        $self->postfix = $postfix;

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

    public function withMethod(string $name): self
    {
        $self = clone $this;
        $self->method = $name;

        return $self;
    }

    public function withRootDirectory(string $directory)
    {
        $self = clone $this;
        $self->directory = $directory;

        return $self;
    }

    public function getBaseClassName(string $postfix = ''): string
    {
        return $this->baseClassName.$postfix;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getClassName(string $postfix = ''): string
    {
        if ($this->postfix !== '' && str_ends_with($this->baseClassName, $this->postfix)) {
            return $this->baseClassName.$postfix;
        }

        return $this->baseClassName.$this->postfix.$postfix;
    }

    public function getClassNameWithNamespace(string $postfix = ''): string
    {
        return $this->getNamespace().'\\'.$this->getClassName($postfix);
    }

    public function getFilePath(): string
    {
        return $this->directory.$this->getClassName().'.php';
    }

    public function getSignalMethods(): array
    {
        return $this->signalMethods;
    }

    public function getQueryMethods(): array
    {
        return $this->queryMethods;
    }

    /**
     * @return array|string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getHandlerMethodName(): string
    {
        return $this->method;
    }

    public function isScheduled(): bool
    {
        return $this->scheduled;
    }
}
