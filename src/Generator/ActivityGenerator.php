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
        $class = new ClassType(
            $context->getClass()
        );
        $class->addImplement($context->getClassInterfaceWithNamespace());

        $method = $class
            ->addMethod('__construct')
            ->setPublic();

        $method->addPromotedParameter('logger')
            ->setPrivate()
            ->setType(LoggerInterface::class);

        $method = $class->addMethod($context->getHandlerMethodName())
            ->setPublic()
            ->setReturnType('string');

        Utils::addParameters($context->getHandlerParameters(), $method);

        $method->addBody(
            \sprintf(
                '$this->logger->info(\'%s\', [%s]);',
                'Something special happens here.',
                implode(
                    ', ',
                    array_map(fn($param) => \sprintf('\'%s\' => %s', $param, '$'.$param), array_keys($context->getHandlerParameters()))
                ),
            )
        );

        $method->addBody('return ?;', ['Success']);

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(LoggerInterface::class),
            $context
        );
    }
}
