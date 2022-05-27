<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Commands;

use Spiral\Console\Command;
use Spiral\TemporalBridge\Preset\PresetRegistryInterface;
use Spiral\TemporalBridge\WorkflowPresetLocatorInterface;
use Symfony\Component\Console\Helper\Table;

final class PresetListCommand extends Command
{
    private const DESCRIPTION_LENGTH = 200;
    protected const NAME = 'temporal:presets';
    protected const DESCRIPTION = 'Show list of available Temporal presets';

    public function perform(
        PresetRegistryInterface $registry,
        WorkflowPresetLocatorInterface $presetLocator
    ): int {
        foreach ($presetLocator->getPresets() as $name => $preset) {
            $registry->register($name, $preset);
        }

        $list = $registry->getList();
        if ($list === []) {
            $this->info('No available Workflow presets found.');

            return self::SUCCESS;
        }

        $table = new Table($this->output);

        $table->setHeaders(['name', 'description']);

        foreach ($list as $name => $preset) {
            $table->addRow([$name, \implode("\n", \str_split($preset->getDescription(), self::DESCRIPTION_LENGTH))]);
        }

        $table->render();

        $this->newLine();
        $this->info('Use the command below to make a workflow: ');
        $this->comment('php app.php temporal:make-preset preset-name MySuperWorkflow');
        $this->newLine();

        return self::SUCCESS;
    }
}
