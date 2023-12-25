<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Commands;

use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\DeclarationLocatorInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

final class InfoCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $locator = $this->mockContainer(DeclarationLocatorInterface::class);
        $locator->shouldReceive('getDeclarations')->andReturnUsing(function () {
            yield WorkflowInterface::class => new \ReflectionClass(Workflow::class);
            yield ActivityInterface::class => new \ReflectionClass(ActivityInterfaceWithWorker::class);
            yield ActivityInterface::class => new \ReflectionClass(ActivityInterfaceWithoutWorker::class);
            yield WorkflowInterface::class => new \ReflectionClass(AnotherWorkflow::class);
        });
    }

    public function testInfo(): void
    {
        $result = $this->runCommand('temporal:info');

        $this->assertSame(
            <<<'OUTPUT'

Workflows
=========

+-----------------+------------------------------------------------------+------------------+
| Name            | Class                                                | Task Queue       |
+-----------------+------------------------------------------------------+------------------+
| fooWorkflow     | Spiral\TemporalBridge\Tests\Commands\Workflow        | worker2          |
|                 | src/Commands/InfoCommandTest.php                     |                  |
| AnotherWorkflow | Spiral\TemporalBridge\Tests\Commands\AnotherWorkflow | default, worker2 |
|                 | src/Commands/InfoCommandTest.php                     |                  |
+-----------------+------------------------------------------------------+------------------+

OUTPUT,
            $result,
        );
    }

    public function testInfoWithActivities(): void
    {
        $result = $this->runCommand('temporal:info', [
            '--show-activities' => true,
        ]);

        $this->assertSame(
            <<<'OUTPUT'

Workflows
=========

+-----------------+------------------------------------------------------+------------------+
| Name            | Class                                                | Task Queue       |
+-----------------+------------------------------------------------------+------------------+
| fooWorkflow     | Spiral\TemporalBridge\Tests\Commands\Workflow        | worker2          |
|                 | src/Commands/InfoCommandTest.php                     |                  |
| AnotherWorkflow | Spiral\TemporalBridge\Tests\Commands\AnotherWorkflow | default, worker2 |
|                 | src/Commands/InfoCommandTest.php                     |                  |
+-----------------+------------------------------------------------------+------------------+

Activities
==========

+------------------------+---------------------------------------------+------------+
| Name                   | Class                                       | Task Queue |
+------------------------+---------------------------------------------+------------+
| fooActivity            | ActivityInterfaceWithWorker::foo            | worker1    |
| bar                    | ActivityInterfaceWithWorker::bar            |            |
+------------------------+---------------------------------------------+------------+
| fooActivity__construct | ActivityInterfaceWithoutWorker::__construct | default    |
| fooActivitybaz         | ActivityInterfaceWithoutWorker::baz         |            |
+------------------------+---------------------------------------------+------------+

OUTPUT,
            $result,
        );
    }
}

#[AssignWorker(taskQueue: 'worker1')]
#[ActivityInterface]
class ActivityInterfaceWithWorker
{
    #[ActivityMethod('fooActivity')]
    public function foo(): void
    {
    }

    #[ActivityMethod]
    public function bar(): void
    {
    }
}


#[ActivityInterface('fooActivity')]
class ActivityInterfaceWithoutWorker
{
    public function __construct()
    {
    }

    #[ActivityMethod]
    public function baz(): void
    {
    }

    private function baf(): void
    {
    }
}

#[AssignWorker(taskQueue: 'worker2')]
#[WorkflowInterface]
class Workflow
{
    #[WorkflowMethod('fooWorkflow')]
    public function handle()
    {
    }
}

#[AssignWorker(taskQueue: 'default')]
#[AssignWorker(taskQueue: 'worker2')]
#[WorkflowInterface]
class AnotherWorkflow
{
    #[WorkflowMethod]
    public function handle()
    {
    }
}
