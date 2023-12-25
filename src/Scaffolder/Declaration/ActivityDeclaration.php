<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Scaffolder\Declaration;

use React\Promise\PromiseInterface;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration\AbstractDeclaration;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

final class ActivityDeclaration extends AbstractDeclaration
{
    public const TYPE = 'activity';

    public function __construct(
        ScaffolderConfig $config,
        string $name,
        ?string $comment = null,
        ?string $namespace = null,
        private ?string $activityName = null,
    ) {
        parent::__construct($config, $name, $comment, $namespace);
    }

    public function declare(): void
    {
        $this->namespace->addUse(ActivityInterface::class);
        $this->namespace->addUse(ActivityMethod::class);
        $this->namespace->addUse(PromiseInterface::class);

        $classAttributeArgs = [];
        if ($this->activityName !== null) {
            $classAttributeArgs['name'] = $this->activityName;
        }

        $this->class->addAttribute(ActivityInterface::class, $classAttributeArgs);
    }

    public function assignWorker(string $worker): void
    {
        $this->namespace->addUse(AssignWorker::class);
        $this->class->addAttribute(AssignWorker::class, ['taskQueue' => $worker]);
    }

    public function addMethod(string $name, string $returnType): void
    {
        $this->class
            ->addMethod($name)
            ->setPublic()
            ->addAttribute(ActivityMethod::class, ['name' => $name])
            ->setReturnType($returnType)
            ->addComment('@return PromiseInterface<' . $returnType . '>')
            ->setBody('// TODO: Implement activity method');
    }
}
