<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Carbon\CarbonInterval;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Log\LoggerInterface;
use Spiral\Files\FilesInterface;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Temporal\Activity\ActivityOptions;
use Temporal\Api\Enums\V1\WorkflowIdReusePolicy;
use Temporal\Exception\Client\WorkflowExecutionAlreadyStartedException;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow\QueryMethod;
use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowMethod;

class WorkFlowGenerator
{
    private bool $scheduled = false;
    private string $namespace;
    private string $baseClassName;
    private string $method = 'handle';
    private array $signalMethods = [];
    private array $queryMethods = [];
    private array $parameters = ['name' => 'string'];

    public function __construct(
        private FilesInterface $files
    ) {
    }

    public function withCronSchedule()
    {
        $this->scheduled = true;

        return $this;
    }

    public function withMethodParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function withSignalMethods(array $methods): self
    {
        $this->signalMethods = $methods;

        return $this;
    }

    public function withQueryMethods(array $methods): self
    {
        $this->queryMethods = $methods;

        return $this;
    }

    public function withNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function withMethod(string $name): self
    {
        $this->method = $name;

        return $this;
    }

    public function generate(string $className, string $path): void
    {
        $this->baseClassName = $className;

        $types = [
            'WorkflowInterface',
            'Workflow',
            'ActivityInterface',
            'Activity',
            'HandlerInterface',
            'Handler',
        ];

        foreach ($types as $type) {
            $method = 'generate'.$type;
            [$namespace, $class] = $this->{$method}(
                $type,
                new PhpNamespace($this->namespace)
            );

            $this->generateFile($namespace, $path.$class->getName().'.php');
        }
    }

    private function generateHandlerInterface(string $classPostfix, PhpNamespace $namespace): array
    {
        $className = $this->baseClassName.$classPostfix;

        $class = ClassType::interface($className);
        $class
            ->addAttribute(\Temporal\Workflow\WorkflowInterface::class);

        $method = $class->addMethod($this->method)
            ->setPublic()
            ->setReturnType('void');

        $this->addParameters($method);

        return [
            $namespace
                ->add($class)
                ->addUse(WorkflowIdReusePolicy::class)
                ->addUse(LoggerInterface::class)
                ->addUse(WorkflowManagerInterface::class),
            $class,
        ];
    }

    private function generateHandler(string $classPostfix, PhpNamespace $namespace): array
    {
        $workflowClassName = str_replace('Workflow', '', $this->baseClassName).'WorkflowInterface';
        $className = $this->baseClassName.$classPostfix;

        $class = new ClassType($className);
        $class->addImplement($namespace->getName().'\\'.$className.'Interface');

        $method = $class
            ->addMethod('__construct')
            ->setPublic();

        $method->addPromotedParameter('manager')
            ->setPrivate()
            ->setType(WorkflowManagerInterface::class);

        $method->addPromotedParameter('logger')
            ->setPrivate()
            ->setType(LoggerInterface::class);

        $method = $class->addMethod('handle')
            ->setReturnType('void');
        $this->addParameters($method);

        $runArgs = implode(', ', array_map(fn($param) => '$'.$param, array_keys($this->parameters)));

        if ($this->scheduled) {
            $body = \sprintf(
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
        } else {
            $body = \sprintf(
                <<<'BODY'
$workflow = $this->manager
    ->create(%s::class);
BODY
                ,
                $workflowClassName
            );
        }

        $method->addBody($body);


        $method->addBody(
            \sprintf(
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
            )
        );

        $method->addBody(
            \sprintf(
                <<<'BODY'

$this->logger->info('Workflow [%s] has been run', [
    'id' => $run->getExecution()->getID(),
    'run_id' => $run->getExecution()->getRunID()
]);
BODY,
                $this->baseClassName
            )
        );

        return [
            $namespace
                ->add($class)
                ->addUse(WorkflowExecutionAlreadyStartedException::class)
                ->addUse(WorkflowIdReusePolicy::class)
                ->addUse(LoggerInterface::class)
                ->addUse(WorkflowManagerInterface::class),
            $class,
        ];
    }

    private function generateActivity(string $classPostfix, PhpNamespace $namespace): array
    {
        $className = $this->baseClassName.$classPostfix;
        $class = new ClassType($className);
        $class->addImplement($namespace->getName().'\\'.$className.'Interface');

        $method = $class
            ->addMethod('__construct')
            ->setPublic();

        $method->addPromotedParameter('logger')
            ->setPrivate()
            ->setType(LoggerInterface::class);

        $method = $class->addMethod($this->method)
            ->setPublic()
            ->setReturnType('string');

        $this->addParameters($method);
        $method->addBody(
            \sprintf(
                '$this->logger->info(\'%s\', [%s]);',
                'Something special happens here.',
                implode(
                    ', ',
                    array_map(fn($param) => \sprintf('\'%s\' => %s', $param, '$'.$param), array_keys($this->parameters))
                ),
            )
        );

        $method->addBody('return ?;', ['Success']);

        return [
            $namespace
                ->add($class)
                ->addUse(LoggerInterface::class),
            $class,
        ];
    }

    private function generateActivityInterface(
        string $classPostfix,
        PhpNamespace $namespace
    ): array {
        $className = $this->baseClassName.$classPostfix;
        $class = ClassType::interface($className);
        $class
            ->addAttribute(\Temporal\Activity\ActivityInterface::class);

        $method = $class->addMethod($this->method)
            ->setPublic()
            ->setReturnType('string')
            ->addAttribute(\Temporal\Activity\ActivityMethod::class);

        $this->addParameters($method);

        return [
            $namespace
                ->add($class)
                ->addUse(\Temporal\Activity\ActivityInterface::class)
                ->addUse(\Temporal\Activity\ActivityMethod::class),
            $class,
        ];
    }

    private function generateWorkflowInterface(
        string $classPostfix,
        PhpNamespace $namespace
    ): array {
        $className = str_replace('Workflow', '', $this->baseClassName).$classPostfix;

        $class = ClassType::interface($className);
        $class
            ->addAttribute(\Temporal\Workflow\WorkflowInterface::class);

        $method = $class->addMethod($this->method)
            ->setPublic()
            ->setReturnType('\Generator')
            ->addAttribute(WorkflowMethod::class);

        $this->addParameters($method);
        $this->generateWorkflowSignalMethods($class);
        $this->generateWorkflowQueryMethods($class);

        return [
            $namespace
                ->add($class)
                ->addUse(\Temporal\Workflow\QueryMethod::class)
                ->addUse(\Temporal\Workflow\SignalMethod::class)
                ->addUse(\Temporal\Workflow\WorkflowInterface::class)
                ->addUse(WorkflowMethod::class),
            $class,
        ];
    }

    private function generateWorkflow(string $classPostfix, PhpNamespace $namespace): array
    {
        $className = str_replace('Workflow', '', $this->baseClassName).$classPostfix;
        $activityClassName = $this->baseClassName.'ActivityInterface';

        $class = new ClassType($className);
        $class->addImplement($namespace->getName().'\\'.$className.'Interface');

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

        $method = $class->addMethod($this->method)
            ->setPublic()
            ->setReturnType('\Generator');

        $this->generateWorkflowSignalMethods($class);
        $this->generateWorkflowQueryMethods($class);

        $this->addParameters($method);

        $method->addBody(
            \sprintf(
                'return yield $this->activity->%s(%s);',
                $this->method,
                implode(', ', array_map(fn($param) => '$'.$param, array_keys($this->parameters)))
            )
        );

        return [
            $namespace
                ->add($class)
                ->addUse(ActivityOptions::class)
                ->addUse(CarbonInterval::class)
                ->addUse(\Temporal\Workflow::class)
                ->addUse(\Temporal\Internal\Workflow\ActivityProxy::class),
            $class,
        ];
    }

    private function generateFile(PhpNamespace $namespace, string $path)
    {
        $file = new \Nette\PhpGenerator\PhpFile;
        $file->addNamespace($namespace);

        $printer = new \Nette\PhpGenerator\PsrPrinter;
        $this->files->write($path, $printer->printFile($file), null, true);
    }

    private function addParameters(Method $method): void
    {
        foreach ($this->parameters as $parameter => $type) {
            $method->addParameter($parameter)->setType($type);
        }
    }

    private function generateWorkflowSignalMethods(ClassType $class)
    {
        foreach ($this->signalMethods as $method) {
            $method = $class->addMethod($method)
                ->setReturnType('void');

            if ($class->isInterface()) {
                $method->addAttribute(SignalMethod::class);
            } else {
                $method->addBody('// Do something special.');
            }
        }
    }

    private function generateWorkflowQueryMethods(ClassType $class)
    {
        foreach ($this->queryMethods as $method => $type) {
            $method = $class->addMethod($method)
                ->setReturnType($type);

            if ($class->isInterface()) {
                $method->addAttribute(QueryMethod::class);
            } else {
                $method->addBody('// Query something special.');
            }
        }
    }
}
