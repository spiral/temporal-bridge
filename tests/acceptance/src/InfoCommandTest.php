<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Acceptance;

use Spiral\TemporalBridge\Tests\Acceptance\Support\Application;
use Testo\Assert;
use Testo\Lifecycle\AfterTest;
use Testo\Test;

/**
 * `temporal:info` over workflows and activities discovered by the tokenizer in the fixture application.
 */
#[Test]
final class InfoCommandTest
{
    private ?Application $app = null;

    public function listsDiscoveredWorkflowsWithTheirTaskQueues(): void
    {
        $app = $this->app();

        $result = $app->console(['temporal:info']);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)
            ->contains('Workflows')
            ->matchesRegex('/billing\.invoice\b.*Billing\\\\InvoiceWorkflow\b.*\bbilling\b/')
            ->contains('app/src/Billing/InvoiceWorkflow.php')
            ->matchesRegex('/RefundWorkflowInterface\b.*Billing\\\\RefundWorkflow\b.*\bmain-queue\b/')
            ->notContains('NotARegisteredClass')
            ->notContains('Activities')
            ->notContains('payment.charge');
    }

    public function listsActivitiesOnRequest(): void
    {
        $app = $this->app();

        $long = $app->console(['temporal:info', '--show-activities']);
        $short = $app->console(['temporal:info', '-a']);

        Assert::same($long->exitCode, 0, (string) $long);
        Assert::string($long->stdout)
            ->contains('Workflows')
            ->contains('billing.invoice')
            ->contains('Activities')
            ->matchesRegex('/payment\.charge\b.*PaymentActivity::charge\b.*\bpayments\b/')
            ->matchesRegex('/payment\.giveBack\b.*PaymentActivity::refund\b/');
        Assert::same($short->exitCode, 0, (string) $short);
        Assert::same($short->stdout, $long->stdout);
    }

    public function showsEmptyTablesForApplicationWithoutDeclarations(): void
    {
        $app = $this->app(withFixtures: false);

        $result = $app->console(['temporal:info', '-a']);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)
            ->contains('Workflows')
            ->contains('Activities')
            ->notContains('Billing');
    }

    public function takesDefaultTaskQueueFromEnvironmentWithoutConfig(): void
    {
        $app = $this->app();
        $app->remove('app/config/temporal.php');

        $result = $app->console(['temporal:info'], env: ['TEMPORAL_TASK_QUEUE' => 'from-env']);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)
            ->matchesRegex('/RefundWorkflowInterface\b.*\bfrom-env\b/')
            ->matchesRegex('/billing\.invoice\b.*\bbilling\b/');
    }

    public function fallsBackToDefaultTaskQueueWithoutConfigAndEnvironment(): void
    {
        $app = $this->app();
        $app->remove('app/config/temporal.php');

        $result = $app->console(['temporal:info']);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)->matchesRegex('/RefundWorkflowInterface\b.*\|\s*default\s*\|/');
    }

    public function rejectsUnknownOption(): void
    {
        $app = $this->app();

        $result = $app->console(['temporal:info', '--bogus']);

        Assert::notSame($result->exitCode, 0, (string) $result);
        Assert::string($result->output())->contains('--bogus');
    }

    #[AfterTest]
    public function destroyApplication(): void
    {
        $this->app?->destroy();
        $this->app = null;
    }

    private function app(bool $withFixtures = true): Application
    {
        return $this->app = Application::create($withFixtures);
    }
}
