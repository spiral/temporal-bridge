<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

/**
 * @internal
 */
final class ActivityInterfaceGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $class = ClassType::interface($context->getClass());
        $class->addAttribute(ActivityInterface::class, ['prefix' => $context->getBaseClass('.')]);

        foreach ($context->getActivityMethods() as $method) {
            $class->addMember($method);
            $method
                ->setBody(null)
                ->addAttribute(ActivityMethod::class);
        }

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(ActivityInterface::class)
                ->addUse(ActivityMethod::class),
            $context
        );
    }
}
