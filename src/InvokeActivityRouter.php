<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge;

use Psr\Container\ContainerExceptionInterface;
use React\Promise\Deferred;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Container;
use Spiral\Snapshots\SnapshotterInterface;
use Temporal\Activity;
use Temporal\Activity\ActivityInfo;
use Temporal\DataConverter\EncodedValues;
use Temporal\Exception\DoNotCompleteOnResultException;
use Temporal\Internal\Activity\ActivityContext;
use Temporal\Internal\Declaration\ActivityInstance;
use Temporal\Internal\Declaration\Prototype\ActivityPrototype;
use Temporal\Internal\ServiceContainer;
use Temporal\Internal\Transport\Router\Route;
use Temporal\Worker\Transport\Command\RequestInterface;
use Temporal\Worker\Transport\RPCConnectionInterface;

class InvokeActivityRouter extends Route
{
    private const ERROR_NOT_FOUND = 'Activity with the specified name "%s" was not registered';

    public function __construct(
        private readonly ServiceContainer $services,
        private readonly RPCConnectionInterface $rpc,
        private readonly FinalizerInterface $finalizer,
        private readonly Container $container
    ) {
    }

    public function getName(): string
    {
        return 'InvokeActivity';
    }

    public function handle(RequestInterface $request, array $headers, Deferred $resolver): void
    {
        $options = $request->getOptions();
        $payloads = $request->getPayloads();
        $heartbeatDetails = null;

        // always in binary format
        $options['info']['TaskToken'] = \base64_decode($options['info']['TaskToken']);

        if (($options['heartbeatDetails'] ?? 0) !== 0) {
            $offset = \count($payloads) - ($options['heartbeatDetails'] ?? 0);

            $heartbeatDetails = EncodedValues::sliceValues($this->services->dataConverter, $payloads, $offset);
            $payloads = EncodedValues::sliceValues($this->services->dataConverter, $payloads, 0, $offset);
        }

        $context = new ActivityContext($this->rpc, $this->services->dataConverter, $payloads, $heartbeatDetails);
        $context = $this->services->marshaller->unmarshal($options, $context);

        $prototype = $this->findDeclarationOrFail($context->getInfo());

        $instance = new ActivityInstance(
            $prototype,
            $this->container->make($prototype->getClass()->getName())
        );

        try {
            Activity::setCurrentContext($context);

            $handler = $instance->getHandler();
            $result = $handler($payloads);

            if ($context->isDoNotCompleteOnReturn()) {
                $resolver->reject(DoNotCompleteOnResultException::create());
            } else {
                $resolver->resolve(EncodedValues::fromValues([$result]));
            }
        } catch (\Throwable $e) {
            $resolver->reject($e);
            $this->handleException($e);
        } finally {
            Activity::setCurrentContext(null);
            $this->finalizer->finalize();
        }
    }

    private function findDeclarationOrFail(ActivityInfo $info): ActivityPrototype
    {
        $activity = $this->services->activities->find($info->type->name);

        if ($activity === null) {
            throw new \LogicException(\sprintf(self::ERROR_NOT_FOUND, $info->type->name));
        }

        return $activity;
    }

    private function handleException(\Throwable $e): void
    {
        try {
            $this->container->get(SnapshotterInterface::class)->register($e);
        } catch (\Throwable $se) {
            // nothing to report
        }
    }
}
