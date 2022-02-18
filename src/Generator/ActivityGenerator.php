<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Log\LoggerInterface;

final class ActivityGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $class = new ClassType($context->getClass());
        $class->addImplement($context->getClassInterfaceWithNamespace());

        $method = $class->addMethod('__construct')->setPublic();

        $method->addPromotedParameter('logger')
            ->setPrivate()
            ->setType(LoggerInterface::class);

        foreach ($context->getActivityMethods() as $method) {
            $class->addMember($method);
            $method
                ->addBody(
                    \sprintf(
                        '$this->logger->info(\'%s\', func_get_args());',
                        'Something special happens here.',
                    )
                )
                ->addBody("\nreturn 'Success';");
        }

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(LoggerInterface::class),
            $context
        );
    }
}
