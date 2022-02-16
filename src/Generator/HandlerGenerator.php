<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Log\LoggerInterface;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Temporal\Api\Enums\V1\WorkflowIdReusePolicy;
use Temporal\Exception\Client\WorkflowExecutionAlreadyStartedException;

final class HandlerGenerator implements FileGeneratorInterface
{
    public function generate(Context $context, PhpNamespace $namespace): PhpCodePrinter
    {
        $class = new ClassType($context->getClassName());
        $class->addImplement($context->getClassNameWithNamespace('Interface'));

        $constructor = $class
            ->addMethod('__construct')
            ->setPublic();

        $constructor->addPromotedParameter('manager')
            ->setPrivate()
            ->setType(WorkflowManagerInterface::class);

        $constructor->addPromotedParameter('logger')
            ->setPrivate()
            ->setType(LoggerInterface::class);

        $method = $class->addMethod('handle')
            ->setReturnType('void');

        Utils::addParameters($context->getParameters(), $method);

        $method->addBody($this->generateWorkflowInitialization($context));
        $method->addBody($this->generateWorkflowSettingBody($context));
        $method->addBody($this->generateLoggerInfoBody($context));

        return new PhpCodePrinter(
            $namespace
                ->add($class)
                ->addUse(WorkflowExecutionAlreadyStartedException::class)
                ->addUse(WorkflowIdReusePolicy::class)
                ->addUse(LoggerInterface::class)
                ->addUse(WorkflowManagerInterface::class),
            $context
        );
    }

    private function generateWorkflowInitialization(Context $context): string
    {
        $workflowClassName = $context->getBaseClassName('WorkflowInterface');

        if ($context->isScheduled()) {
            return \sprintf(
                <<<'BODY'
$workflow = $this->manager
    ->createScheduled(
        %s::class, 
        '%s'
    );
BODY
                ,
                $workflowClassName,
                '* * * * *'
            );
        }

        return \sprintf(
            <<<'BODY'
$workflow = $this->manager
    ->create(%s::class);
BODY
            ,
            $workflowClassName
        );
    }

    private function generateWorkflowSettingBody(Context $context): string
    {
        $runArgs = implode(', ', array_map(fn($param) => '$'.$param, array_keys($context->getParameters())));

        return \sprintf(
            <<<'BODY'

// $workflow->assignId(
//     'operation-id', 
//     WorkflowIdReusePolicy::WORKFLOW_ID_REUSE_POLICY_ALLOW_DUPLICATE_FAILED_ONLY
// );

// $workflow->withWorkflowRunTimeout(\Carbon\CarbonInterval::minutes(10))
//    ->withWorkflowTaskTimeout(\Carbon\CarbonInterval::minute())
//    ->withWorkflowExecutionTimeout(\Carbon\CarbonInterval::minutes(5));

// $workflow->maxRetryAttempts(5)
//      ->backoffRetryCoefficient(1.5)
//      ->initialRetryInterval(\Carbon\CarbonInterval::seconds(5))
//      ->maxRetryInterval(\Carbon\CarbonInterval::seconds(20));

try {
    $run = $workflow->run(%s);
} catch (WorkflowExecutionAlreadyStartedException $e) {
    $this->logger->error('Workflow has been already started.', [
        'name' => $workflow->getWorkflowType()
    ]);
}
BODY,
            $runArgs
        );
    }

    /**
     * @param Context $context
     * @return string
     */
    private function generateLoggerInfoBody(Context $context): string
    {
        return \sprintf(
            <<<'BODY'

$this->logger->info('Workflow [%s] has been run', [
    'id' => $run->getExecution()->getID(),
    'run_id' => $run->getExecution()->getRunID()
]);
BODY,
            $context->getBaseClassName()
        );
    }
}
