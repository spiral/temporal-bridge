<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Generator\Preset;

use Spiral\TemporalBridge\Attribute\WorkflowPreset;
use Spiral\TemporalBridge\Generator\ActivityGenerator;
use Spiral\TemporalBridge\Generator\ActivityInterfaceGenerator;
use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\HandlerGenerator;
use Spiral\TemporalBridge\Generator\HandlerInterfaceGenerator;
use Spiral\TemporalBridge\Generator\SignalWorkflowGenerator;
use Spiral\TemporalBridge\Generator\WorkflowInterfaceGenerator;

#[WorkflowPreset('signal')]
final class SignalWorkflow implements PresetInterface
{
    public function getDescription(): ?string
    {
        return 'Workflow with signals';
    }

    public function generators(Context $context): array
    {
        $generators = [
            'WorkflowInterface' => new WorkflowInterfaceGenerator(),
            'Workflow' => new SignalWorkflowGenerator(),
        ];

        if ($context->hasActivity()) {
            $generators = \array_merge($generators, [
                'ActivityInterface' => new ActivityInterfaceGenerator(),
                'Activity' => new ActivityGenerator(),
            ]);
        }

        if ($context->hasHandler()) {
            $generators = \array_merge($generators, [
                'HandlerInterface' => new HandlerInterfaceGenerator(),
                'Handler' => new HandlerGenerator(),
            ]);
        }

        return $generators;
    }
}
