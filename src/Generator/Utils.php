<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow\QueryMethod;
use Temporal\Workflow\SignalMethod;

final class Utils
{
    public static function addParameters(array $parameters, Method $method): void
    {
        foreach ($parameters as $parameter => $type) {
            $method->addParameter($parameter)->setType($type);
        }
    }

    public static function generateWorkflowSignalMethods(array $signalMethods, ClassType $class): void
    {
        foreach ($signalMethods as $method) {
            $params = null;
            if (strpos($method, ',') !== false) {
                [$method, $params] = explode(',', $method, 2);
            }

            $method = $class->addMethod($method)
                ->setPublic()
                ->setReturnType('void');

            if ($params) {
                static::addParameters(static::parseParameters(explode(',', $params)), $method);
            }

            if ($class->isInterface()) {
                $method->addAttribute(SignalMethod::class);
            } else {
                $method->addBody('// Do something special.');
            }
        }
    }

    public static function generateWorkflowQueryMethods(array $queryMethods, ClassType $class): void
    {
        foreach ($queryMethods as $method => $type) {
            $method = $class->addMethod($method)
                ->setPublic()
                ->setReturnType($type);

            if ($class->isInterface()) {
                $method->addAttribute(QueryMethod::class);
            } else {
                $method->addBody('// Query something special.');
            }
        }
    }

    /**
     * @param string[] $methods
     * @return Method[]
     */
    public static function parseMethods(array $methods): array
    {
        $result = [];

        foreach ($methods as $method) {
            $params = '';
            if (strpos($method, ',') !== false) {
                [$method, $params] = explode(',', $method, 2);
            }

            if (strpos($method, ':') !== false) {
                [$method, $type] = explode(':', $method, 2);
            }

            $type ??= 'void';

            $result[$method] = (new Method($method))
                ->setPublic()
                ->setReturnType($type);
            $result[$method]->setParameters(self::parseParameters(explode(',', $params)));
        }

        return $result;
    }

    /**
     * @param string[] $parameters
     * @return Parameter[]
     */
    public static function parseParameters(array $parameters): array
    {
        $params = [];

        foreach ($parameters as $param) {
            $type = null;
            if (strpos($param, ':') !== false) {
                [$param, $type] = explode(':', $param, 2);
            }

            if (empty($param)) {
                continue;
            }

            $type ??= 'string';
            $params[$param] = (new Parameter($param))->setType($type);
        }

        return $params;
    }

    public static function buildMethodArgs(array $args): string
    {
        return implode(', ', array_map(fn($param) => '$'.$param, array_keys($args)));
    }

    public static function initializeActivityProperty(ClassType $class, Context $context): void
    {
        $activityClass = $context->getBaseClassInterface('Activity');
        $activityName = $context->getBaseClass().'.handle';

        $class->addProperty('activity')
            ->setPrivate()
            ->setType(ActivityProxy::class)
            ->addComment(
                $context->hasActivity()
                    ? \sprintf('@var %s|%s', 'ActivityProxy', $activityClass)
                    : \sprintf('@var %s', 'ActivityProxy')
            );

        if ($class->hasMethod('__construct')) {
            $constructor = $class->getMethod('__construct');
        } else {
            $constructor = $class->addMethod('__construct')->setPublic();
        }

        $constructor->addBody(
            \sprintf(
                <<<'BODY'
$this->activity = Workflow::newActivityStub(
    %s,
    ActivityOptions::new()
        ->withScheduleToCloseTimeout(CarbonInterval::seconds(10))
);
BODY,
                $context->hasActivity() ? $activityClass.'::class' : "'$activityName'"
            )
        );
    }
}
