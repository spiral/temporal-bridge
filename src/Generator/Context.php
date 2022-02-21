<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;

final class Context
{
    private const INTERFACE = 'Interface';
    private const HANDLER_METHOD = 'handle';

    private bool $withActivity = false;
    private bool $withHandler = false;
    private bool $scheduled = false;
    private string $handlerMethod = self::HANDLER_METHOD;
    /** @var array<Method> */
    private array $activityMethods = [];
    /** @var array<Method> */
    private array $signalMethods = [];
    /** @var array<Method> */
    private array $queryMethods = [];
    /** @var array<Parameter> */
    private array $handlerParameters = [];
    private string $classPostfix = '';

    public function __construct(
        private string $directory,
        private string $namespace,
        private string $baseClass,
    ) {
    }

    public function withClassPostfix(string $postfix): self
    {
        $this->classPostfix = str_ends_with($this->baseClass, 'Workflow')
            ? str_replace('Workflow', '', $postfix)
            : $postfix;

        return $this;
    }

    public function withCronSchedule(): self
    {
        $this->scheduled = true;

        return $this;
    }

    /**
     * @param array<Parameter> $parameters
     */
    public function withMethodParameters(array $parameters): self
    {
        $this->handlerParameters = $parameters;

        return $this;
    }

    /**
     * @param array<Method> $methods
     */
    public function withSignalMethods(array $methods): self
    {
        $this->signalMethods = $methods;

        return $this;
    }

    /**
     * @param array<Method> $methods
     */
    public function withQueryMethods(array $methods): self
    {
        $this->queryMethods = $methods;

        return $this;
    }

    public function withHandlerMethod(string $name): self
    {
        $this->handlerMethod = $name;

        return $this;
    }

    public function withActivity(): self
    {
        $this->withActivity = true;

        return $this;
    }

    /**
     * @param array<Method> $methods
     */
    public function withActivityMethods(array $methods): self
    {
        $this->activityMethods = $methods;

        return $this;
    }

    public function withHandler(): self
    {
        $this->withHandler = true;

        return $this;
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
     * @return array<Method>
     */
    public function getSignalMethods(): array
    {
        return \array_map(fn($method) => clone $method, $this->signalMethods);
    }

    /**
     * Get required query methods
     * @return array<Method>
     */
    public function getQueryMethods(): array
    {
        return \array_map(fn($method) => clone $method, $this->queryMethods);
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
     * Check if activity has defined methods
     */
    public function hasActivityMethods(): bool
    {
        return $this->activityMethods !== [];
    }

    /**
     * Get activity methods
     * @return array<Method>
     */
    public function getActivityMethods(): array
    {
        if (! $this->hasActivityMethods()) {
            return [$this->handlerMethod => $this->getHandlerMethod()];
        }

        return \array_map(fn($method) => clone $method, $this->activityMethods);
    }

    /**
     * Check if workflow should have handler classes
     */
    public function hasHandler(): bool
    {
        return $this->withHandler;
    }

    /**
     * Check if default handler method is changed
     */
    public function isHandlerMethodNameChanged(): bool
    {
        return $this->handlerMethod !== self::HANDLER_METHOD;
    }

    /**
     * Get workflow handler method
     */
    public function getHandlerMethod(): Method
    {
        return (new Method($this->handlerMethod))
            ->setPublic()
            ->setReturnType('\Generator')
            ->setParameters($this->handlerParameters);
    }


    /**
     * Get workflow handler method name
     */
    public function getHandlerMethodName(): string
    {
        return $this->handlerMethod;
    }
}
