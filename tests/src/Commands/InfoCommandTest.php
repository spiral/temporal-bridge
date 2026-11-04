<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Commands;

use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Declaration\DeclarationDto;
use Spiral\TemporalBridge\Declaration\DeclarationType;
use Spiral\TemporalBridge\DeclarationRegistryInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class InfoCommandTest extends TestCase
{
    public function testInfo(): void
    {
        $result = $this->runCommand('temporal:info');

        Assert::string($result)->ignoringLineEndings()->same(<<<'OUTPUT'

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

                OUTPUT);
    }

    public function testInfoWithActivities(): void
    {
        $result = $this->runCommand('temporal:info', [
            '--show-activities' => true,
        ]);

        Assert::string($result)->ignoringLineEndings()->same(<<<'OUTPUT'

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

                +----------------+-------------------------------------+------------+
                | Name           | Class                               | Task Queue |
                +----------------+-------------------------------------+------------+
                | fooActivity    | ActivityInterfaceWithWorker::foo    | worker1    |
                | bar            | ActivityInterfaceWithWorker::bar    |            |
                +----------------+-------------------------------------+------------+
                | fooActivitybaz | ActivityInterfaceWithoutWorker::baz | default    |
                +----------------+-------------------------------------+------------+

                OUTPUT);
    }

    public function testInfoWithExplicitTaskQueues(): void
    {
        $locator = $this->mockContainer(DeclarationRegistryInterface::class);
        $locator->shouldReceive('getDeclarationList')->andReturn([
            new DeclarationDto(
                type: DeclarationType::Workflow,
                class: new \ReflectionClass(Workflow::class),
                taskQueue: 'custom',
            ),
            new DeclarationDto(
                type: DeclarationType::Activity,
                class: new \ReflectionClass(ActivityInterfaceWithoutWorker::class),
                taskQueue: 'custom',
            ),
        ]);

        $result = $this->runCommand('temporal:info', [
            '--show-activities' => true,
        ]);

        Assert::string($result)->ignoringLineEndings()->same(<<<'OUTPUT'

                Workflows
                =========

                +-------------+-----------------------------------------------+------------+
                | Name        | Class                                         | Task Queue |
                +-------------+-----------------------------------------------+------------+
                | fooWorkflow | Spiral\TemporalBridge\Tests\Commands\Workflow | custom     |
                |             | src/Commands/InfoCommandTest.php              |            |
                +-------------+-----------------------------------------------+------------+

                Activities
                ==========

                +----------------+-------------------------------------+------------+
                | Name           | Class                               | Task Queue |
                +----------------+-------------------------------------+------------+
                | fooActivitybaz | ActivityInterfaceWithoutWorker::baz | custom     |
                +----------------+-------------------------------------+------------+

                OUTPUT);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $locator = $this->mockContainer(DeclarationRegistryInterface::class);
        $locator->shouldReceive('getDeclarationList')->andReturnUsing(static function () {
            yield new DeclarationDto(
                type: DeclarationType::Workflow,
                class: new \ReflectionClass(Workflow::class),
            );
            yield new DeclarationDto(
                type: DeclarationType::Activity,
                class: new \ReflectionClass(ActivityInterfaceWithWorker::class),
            );
            yield new DeclarationDto(
                type: DeclarationType::Activity,
                class: new \ReflectionClass(ActivityInterfaceWithoutWorker::class),
            );
            yield new DeclarationDto(
                type: DeclarationType::Workflow,
                class: new \ReflectionClass(AnotherWorkflow::class),
            );
        });
    }
}

#[AssignWorker(taskQueue: 'worker1')]
#[ActivityInterface]
class ActivityInterfaceWithWorker
{
    #[ActivityMethod('fooActivity')]
    public function foo(): void {}

    #[ActivityMethod]
    public function bar(): void {}
}


#[ActivityInterface('fooActivity')]
class ActivityInterfaceWithoutWorker
{
    public function __construct() {}

    #[ActivityMethod]
    public function baz(): void {}

    private function baf(): void {}
}

#[AssignWorker(taskQueue: 'worker2')]
#[WorkflowInterface]
class Workflow
{
    #[WorkflowMethod('fooWorkflow')]
    public function handle(): void {}
}

#[AssignWorker(taskQueue: 'default')]
#[AssignWorker(taskQueue: 'worker2')]
#[WorkflowInterface]
class AnotherWorkflow
{
    #[WorkflowMethod]
    public function handle(): void {}
}
