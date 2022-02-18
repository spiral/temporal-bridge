<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Spiral\TemporalBridge\Workflow\RunningWorkflow;

class HandlerInterfaceGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $class = ClassType::interface(
            $context->getClass()
        );

        $method = $class->addMethod($context->getHandlerMethodName())
            ->setPublic()
            ->setReturnType(RunningWorkflow::class);

        Utils::addParameters($context->getHandlerParameters(), $method);

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(RunningWorkflow::class),
            $context
        );
    }
}
