<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Acceptance;

use Spiral\TemporalBridge\Tests\Acceptance\Support\Application;
use Testo\Assert;
use Testo\Lifecycle\AfterTest;
use Testo\Test;

/**
 * `create:workflow` generates a workflow class that the application then discovers.
 */
#[Test]
final class CreateWorkflowCommandTest
{
    private const FILE = 'app/src/Endpoint/Temporal/Workflow/ShippingWorkflow.php';

    private ?Application $app = null;

    public function generatesWorkflowClass(): void
    {
        $app = $this->app();

        $result = $app->console(['create:workflow', 'Shipping']);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)->contains('ShippingWorkflow')->contains(self::FILE);
        Assert::true($app->exists(self::FILE), (string) $result);
        Assert::string($app->read(self::FILE))
            ->contains('namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Workflow;')
            ->contains('class ShippingWorkflow')
            ->contains('#[WorkflowInterface]')
            ->contains('#[WorkflowMethod]')
            ->notContains('AssignWorker');
        Assert::same($app->lint(self::FILE)->exitCode, 0);
    }

    public function generatesWorkflowClassWithOptions(): void
    {
        $app = $this->app();

        $result = $app->console([
            'create:workflow', 'Shipping',
            '--worker', 'shipping',
            '--workflow-name', 'shipping.run',
            '--query-method', 'status:string',
            '-c', 'progress',
            '--signal-method', 'cancel',
            '-s', 'pause',
        ]);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($app->read(self::FILE))
            ->contains("#[AssignWorker(taskQueue: 'shipping')]")
            ->contains("#[WorkflowMethod(name: 'shipping.run')]")
            ->matchesRegex('/#\[QueryMethod]\s+public function status\(\): string/')
            ->matchesRegex('/#\[QueryMethod]\s+public function progress\(\): mixed/')
            ->matchesRegex('/#\[SignalMethod]\s+public function cancel\(\): void/')
            ->matchesRegex('/#\[SignalMethod]\s+public function pause\(\): void/');
        Assert::same($app->lint(self::FILE)->exitCode, 0);
    }

    public function generatedWorkflowIsDiscovered(): void
    {
        $app = $this->app();
        $app->console(['create:workflow', 'Shipping', '-w', 'shipping', '-a', 'shipping.run']);

        $result = $app->console(['temporal:info']);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)
            ->matchesRegex('/shipping\.run\b.*Workflow\\\\ShippingWorkflow\b.*\bshipping\b/')
            ->contains('billing.invoice');
    }

    public function asksForMissingName(): void
    {
        $app = $this->app();

        $result = $app->console(['create:workflow'], input: "Shipping\n");

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)->contains('name');
        Assert::true($app->exists(self::FILE), (string) $result);
    }

    public function failsWithoutNameInNonInteractiveMode(): void
    {
        $app = $this->app();
        $before = $app->sourceFiles();

        $result = $app->console(['create:workflow', '--no-interaction']);

        Assert::notSame($result->exitCode, 0, (string) $result);
        Assert::string($result->output())->contains('name');
        Assert::same($app->sourceFiles(), $before);
    }

    public function keepsExistingFile(): void
    {
        $app = $this->app();
        $app->console(['create:workflow', 'Shipping', '-s', 'cancel']);
        $original = $app->read(self::FILE);

        $result = $app->console(['create:workflow', 'Shipping']);

        Assert::string($result->output())->contains('already exists');
        Assert::same($app->read(self::FILE), $original);
    }

    public function rejectsUnknownOption(): void
    {
        $app = $this->app();

        $result = $app->console(['create:workflow', 'Shipping', '--bogus']);

        Assert::notSame($result->exitCode, 0, (string) $result);
        Assert::string($result->output())->contains('--bogus');
        Assert::false($app->exists(self::FILE));
    }

    #[AfterTest]
    public function destroyApplication(): void
    {
        $this->app?->destroy();
        $this->app = null;
    }

    private function app(): Application
    {
        return $this->app = Application::create();
    }
}
