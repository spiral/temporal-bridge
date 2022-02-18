<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

final class ActivityInterfaceGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $class = ClassType::interface(
            $context->getClass()
        );

        $class
            ->addAttribute(
                ActivityInterface::class,
                ['prefix' => $context->getBaseClass('.')]
            );

        $method = $class->addMethod($context->getHandlerMethodName())
            ->setPublic()
            ->setReturnType('string')
            ->addAttribute(ActivityMethod::class);

        Utils::addParameters($context->getHandlerParameters(), $method);

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(ActivityInterface::class)
                ->addUse(ActivityMethod::class),
            $context
        );
    }
}
