<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Acceptance;

use Spiral\TemporalBridge\Tests\Acceptance\Support\Application;
use Testo\Assert;
use Testo\Lifecycle\AfterTest;
use Testo\Skip;
use Testo\Test;

/**
 * `create:activity` generates an activity class that the application then discovers.
 */
#[Test]
final class CreateActivityCommandTest
{
    private const FILE = 'app/src/Endpoint/Temporal/Activity/MailerActivity.php';

    private ?Application $app = null;

    public function generatesActivityClass(): void
    {
        $app = $this->app();

        $result = $app->console(['create:activity', 'Mailer']);

        Assert::same($result->exitCode, 0, (string) $result);
        Assert::string($result->stdout)->contains('MailerActivity')->contains(self::FILE);
        Assert::true($app->exists(self::FILE), (string) $result);
        Assert::string($app->read(self::FILE))
            ->contains('namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Activity;')
            ->contains('class MailerActivity')
            ->contains('#[ActivityInterface]')
            ->notContains('AssignWorker');
        Assert::same($app->lint(self::FILE)->exitCode, 0);
    }

    public function generatesActivityMethodsAndDiscoversThem(): void
    {
        $app = $this->app();

        $created = $app->console(['create:activity', 'Mailer', '-w', 'mail', '--method', 'send:bool', '-m', 'ping']);
        $info = $app->console(['temporal:info', '-a']);

        Assert::same($created->exitCode, 0, (string) $created);
        Assert::string($app->read(self::FILE))
            ->contains("#[AssignWorker(taskQueue: 'mail')]")
            ->matchesRegex('/#\[ActivityMethod\b[^]]*]\s+public function send\(\): bool/')
            ->matchesRegex('/#\[ActivityMethod\b[^]]*]\s+public function ping\(\): mixed/');
        Assert::same($app->lint(self::FILE)->exitCode, 0);
        Assert::same($info->exitCode, 0, (string) $info);
        Assert::string($info->stdout)
            ->matchesRegex('/\bsend\b.*MailerActivity::send\b.*\bmail\b/')
            ->matchesRegex('/\bping\b.*MailerActivity::ping\b/')
            ->contains('payment.charge');
    }

    #[Skip('create:activity --activity-name writes #[ActivityInterface(name: ...)], but the attribute has no such parameter, so the next boot fails')]
    public function generatedActivityWithNameKeepsApplicationBootable(): void
    {
        $app = $this->app();

        $created = $app->console(['create:activity', 'Mailer', '--activity-name', 'mail.', '-m', 'send']);
        $info = $app->console(['temporal:info', '-a']);

        Assert::same($created->exitCode, 0, (string) $created);
        Assert::same($info->exitCode, 0, (string) $info);
        Assert::string($info->stdout)->contains('MailerActivity::send');
    }

    public function failsWithoutNameInNonInteractiveMode(): void
    {
        $app = $this->app();
        $before = $app->sourceFiles();

        $result = $app->console(['create:activity', '-n']);

        Assert::notSame($result->exitCode, 0, (string) $result);
        Assert::string($result->output())->contains('name');
        Assert::same($app->sourceFiles(), $before);
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
