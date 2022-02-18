<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
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


    public static function parseParameters(array $parameters): array
    {
        $params = [];

        foreach ($parameters as $param) {
            $type = null;
            if (strpos($param, ':') !== false) {
                [$param, $type] = explode(':', $param, 2);
            }

            $type ??= 'string';
            $params[$param] = $type;
        }

        return $params;
    }

    public static function buildMethodArgs(array $args): string
    {
        return implode(', ', array_map(fn($param) => '$'.$param, array_keys($args)));
    }
}
