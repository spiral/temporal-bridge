<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Log\LoggerInterface;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Temporal\Api\Enums\V1\WorkflowIdReusePolicy;
use Temporal\Workflow\WorkflowInterface;

class HandlerInterfaceGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $class = ClassType::interface(
            $context->getClass()
        );

        $method = $class->addMethod($context->getHandlerMethodName())
            ->setPublic()
            ->setReturnType('void');

        Utils::addParameters($context->getHandlerParameters(), $method);

        return new PhpCodePrinter(
            $namespace
                ->add($class),
            $context
        );
    }
}
