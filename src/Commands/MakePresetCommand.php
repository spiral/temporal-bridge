<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Console\Command;
use Spiral\TemporalBridge\Generator\Generator;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\WorkflowPresetLocatorInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

final class MakePresetCommand extends Command
{
    use WithContext;

    protected const NAME = 'temporal:make-preset';
    protected const DESCRIPTION = 'Make a new Temporal workflow preset';
    protected const ARGUMENTS = [
        ['name', InputArgument::REQUIRED, 'Workflow name'],
        ['preset', InputArgument::REQUIRED, 'Workflow preset'],
    ];

    public function perform(
        Generator $generator,
        PresetRegistryInterface $registry,
        WorkflowPresetLocatorInterface $presetLocator
    ): int {
        foreach ($presetLocator->getPresets() as $name => $preset) {
            $registry->register($name, $preset);
        }

        $context = $this->getContext();
        $presetName = $this->argument('preset');

        $preset = $registry->findByName($presetName);
        $preset->init($context);
        $generators = $preset->generators($context);

        if ($generators === []) {
            $this->error(\sprintf('Generators for preset [%s] are not found.', $presetName));
            return self::INVALID;
        }

        if ($this->verifyExistsWorkflow($context)) {
            return self::SUCCESS;
        }

        \assert($this->output instanceof OutputInterface);

        $generator->generate(
            $this->output,
            $context,
            $generators
        );

        return self::SUCCESS;
    }
}
