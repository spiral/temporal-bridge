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
        $activityClass = $context->getBaseClassInterface('Activity');
        $activityName = $context->getBaseClass().'.handle';

        $class = new ClassType($context->getClass());
        $class->addImplement($context->getClassInterfaceWithNamespace());

        $class->addProperty('activity')
            ->setPrivate()
            ->setType(ActivityProxy::class)
            ->addComment(
                $context->hasActivity()
                    ? \sprintf('@var %s|%s', 'ActivityProxy', $activityClass)
                    : \sprintf('@var %s', 'ActivityProxy')
            );

        $class->addMethod('__construct')
            ->setPublic()
            ->addBody($this->generatePropertyInitialization(
                $context->hasActivity() ? $activityClass.'::class' : "'$activityName'"
            ));

        $class->addMember($handlerMethod = $context->getHandlerMethod());

        $handlerMethod->addBody(
            \sprintf(
                'return yield $this->activity->%s(%s);',
                $context->getHandlerMethodName(),
                Utils::buildMethodArgs($handlerMethod->getParameters())
            )
        );

        foreach ($context->getSignalMethods() as $method) {
            $class->addMember($method->addBody('// Signal about something special.'));
        }

        foreach ($context->getQueryMethods() as $method) {
            $class->addMember($method->addBody('// Query something special.'));
        }

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(CarbonInterval::class)
                ->addUse(ActivityOptions::class)
                ->addUse(ActivityProxy::class)
                ->addUse(Workflow::class),
            $context
        );
    }

    private function generatePropertyInitialization(string $activityName): string
    {
        return \sprintf(
            <<<'BODY'
$this->activity = Workflow::newActivityStub(
    %s,
    ActivityOptions::new()
        ->withScheduleToCloseTimeout(CarbonInterval::seconds(10))
);
BODY,
            $activityName
        );
    }
}
