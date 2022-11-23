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
        $class = new \Nette\PhpGenerator\InterfaceType($context->getClass(), $namespace);
        $class->addAttribute(ActivityInterface::class, ['prefix' => $context->getBaseClass('.')]);

        foreach ($context->getActivityMethods() as $method) {
            $class->addMember($method);
            $method
                ->setBody('')
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
