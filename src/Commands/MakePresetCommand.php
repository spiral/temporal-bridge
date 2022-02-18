<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Console\Command;
use Spiral\TemporalBridge\Generator\Generator;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\WorkflowPresetLocatorInterface;
use Symfony\Component\Console\Input\InputArgument;

final class MakePresetCommand extends Command
{
    use WithContext;

    protected const NAME = 'temporal:make-preset';
    protected const DESCRIPTION = 'Make a new Temporal workflow preset';

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
            $this->output->writeln(\sprintf('<error>Generators for preset [%s] are not found.</error>', $presetName));
            return self::INVALID;
        }

        if ($this->verifyExistsWorkflow($context)) {
            return self::SUCCESS;
        }

        $generator->generate(
            $this->output,
            $context,
            $generators
        );

        return self::SUCCESS;
    }

    protected const ARGUMENTS = [
        ['preset', InputArgument::REQUIRED, 'Workflow preset'],
        ['name', InputArgument::REQUIRED, 'Workflow name'],
    ];
}
