<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Carbon\CarbonInterval;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Temporal\Activity\ActivityOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow;

final class WorkflowGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $activityClassName = $context->getBaseClassInterface('Activity');

        $class = new ClassType(
            $context->getClass()
        );

        $class->addImplement($context->getClassInterfaceWithNamespace());

        $class->addProperty('activity')
            ->setPrivate()
            ->setType(ActivityProxy::class)
            ->addComment(\sprintf('@var %s|%s', 'ActivityProxy', $activityClassName));

        $class->addMethod('__construct')
            ->setPublic()
            ->addBody(
                \sprintf(
                    <<<'BODY'
$this->activity = Workflow::newActivityStub(
    %s,
    ActivityOptions::new()
        ->withScheduleToCloseTimeout(CarbonInterval::seconds(10))
);
BODY,
                    $activityClassName.'::class'
                )
            );

        $method = $class->addMethod($context->getHandlerMethodName())
            ->setPublic()
            ->setReturnType('\Generator');

        Utils::generateWorkflowSignalMethods($context->getSignalMethods(), $class);
        Utils::generateWorkflowQueryMethods($context->getQueryMethods(), $class);
        Utils::addParameters($context->getHandlerParameters(), $method);

        $method->addBody(
            \sprintf(
                'return yield $this->activity->%s(%s);',
                $context->getHandlerMethodName(),
                Utils::buildMethodArgs($context->getHandlerParameters())
            )
        );

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(ActivityOptions::class)
                ->addUse(CarbonInterval::class)
                ->addUse(Workflow::class)
                ->addUse(ActivityProxy::class),
            $context
        );
    }
}
