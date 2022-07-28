<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Carbon\CarbonInterval;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Temporal\Activity\ActivityOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow;

final class WorkflowGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $class = new ClassType($context->getClass());
        $class->addImplement($context->getClassInterfaceWithNamespace());

        Utils::initializeActivityProperty($class, $context);

        $class->addMember($handlerMethod = $context->getHandlerMethod());

        $taskQueue = $context->getTaskQueue();
        if ($taskQueue !== null) {
            $class->addAttribute(AssignWorker::class, ['name' => $taskQueue]);
        }

        foreach ($context->getActivityMethods() as $method) {
            $handlerMethod->addBody(
                \sprintf(
                    'yield $this->activity->%s(%s);',
                    $method->getName(),
                    Utils::buildMethodArgs($method->getParameters())
                )
            );
        }

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
                ->addUse(Workflow::class)
                ->addUse(AssignWorker::class),
            $context
        );
    }
}
