<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Container;
use Temporal\Internal\ServiceContainer;
use Temporal\Internal\Transport\RouterInterface;
use Temporal\Worker\Transport\RPCConnectionInterface;
use Temporal\Worker\WorkerInterface;
use Temporal\Worker\WorkerOptions;

class Worker extends \Temporal\Worker\Worker
{
    public function __construct(
        string $taskQueue,
        WorkerOptions $options,
        private readonly ServiceContainer $services,
        private readonly RPCConnectionInterface $rpc,
        private readonly FinalizerInterface $finalizer,
        private readonly Container $container
    ) {
        parent::__construct($taskQueue, $options, $services, $rpc);
    }

    public function registerActivity(\ReflectionClass $activity): WorkerInterface
    {
        foreach ($this->services->activitiesReader->fromClass($activity->getName()) as $proto) {
            $this->services->activities->add($proto, false);
        }

        return $this;
    }

    protected function createRouter(): RouterInterface
    {
        $router = parent::createRouter();

        $router->add(
            new InvokeActivityRouter(
                $this->services,
                $this->rpc,
                $this->finalizer,
                $this->container,
            ),
            true
        );

        return $router;
    }

}
