<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Spiral\TemporalBridge\Tests\TestCase;

class WorkFlowGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $this->runCommand('temporal:make-workflow', [
            'name' => 'PingSiteWorkflow',
            '-p' => ['url:string', 'name:string']
        ]);

        $this->runCommand('temporal:make-workflow', [
            'name' => 'PingSiteScheduled',
            '--scheduled' => '*/5 * * * *',
            '--with-handler' => true
        ]);

        $this->runCommand('temporal:make-workflow', [
            'name' => 'WithSignal',
            '-s' => ['addName,name', 'exit']
        ]);
    }
}
