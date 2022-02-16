<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Temporal\Workflow\QueryMethod;
use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

final class WorkflowInterfaceGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $className = $context->getClassName();

        $class = ClassType::interface($className);
        $class
            ->addAttribute(WorkflowInterface::class);

        $method = $class->addMethod($context->getHandlerMethodName())
            ->setPublic()
            ->setReturnType('\Generator')
            ->addAttribute(WorkflowMethod::class);

        Utils::generateWorkflowSignalMethods($context->getSignalMethods(), $class);
        Utils::generateWorkflowQueryMethods($context->getQueryMethods(), $class);
        Utils::addParameters($context->getParameters(), $method);

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(QueryMethod::class)
                ->addUse(SignalMethod::class)
                ->addUse(WorkflowInterface::class)
                ->addUse(WorkflowMethod::class),
            $context
        );
    }
}
