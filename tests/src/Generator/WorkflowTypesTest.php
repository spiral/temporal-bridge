<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Spiral\TemporalBridge\Tests\TestCase;

class WorkflowTypesTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = $this->getDirectoryByAlias('app').'src/Workflow/';
    }

    public function testMakesWorkflowWithoutActivityAndHandler()
    {
        $this->runCommand('temporal:make-workflow', [
            'name' => 'PingSite',
        ]);

        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflow.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflowInterface.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteActivity.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteActivityInterface.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteHandler.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteHandlerInterface.php');
    }

    public function testMakesWorkflowWithActivityWithoutHandler()
    {
        $this->runCommand('temporal:make-workflow', [
            'name' => 'PingSite',
            '--with-activity' => true,
        ]);

        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflow.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflowInterface.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteActivity.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteActivityInterface.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteHandler.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteHandlerInterface.php');
    }

    public function testMakesWorkflowWithActivityAndHandler()
    {
        $this->runCommand('temporal:make-workflow', [
            'name' => 'PingSite',
            '--with-activity' => true,
            '--with-handler' => true,
        ]);

        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflow.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflowInterface.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteActivity.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteActivityInterface.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteHandler.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteHandlerInterface.php');
    }

    public function testMakesWorkflowWithoutActivityWithHandler()
    {
        $this->runCommand('temporal:make-workflow', [
            'name' => 'PingSite',
            '--with-handler' => true,
        ]);

        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflow.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteWorkflowInterface.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteActivity.php');
        $this->assertFileDoesNotExist($this->dir.'PingSite/PingSiteActivityInterface.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteHandler.php');
        $this->assertFileExists($this->dir.'PingSite/PingSiteHandlerInterface.php');
    }

    protected function tearDown(): void
    {
        $this->cleanupDirectories($this->dir);

        parent::tearDown();
    }
}
