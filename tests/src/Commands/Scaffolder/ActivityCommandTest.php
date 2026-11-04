<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Commands\Scaffolder;

use Mockery\MockInterface;
use Spiral\Files\FilesInterface;
use Spiral\TemporalBridge\Tests\TestCase;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class ActivityCommandTest extends TestCase
{
    private FilesInterface|MockInterface $files;

    public function testGenerate(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Activity/PaymentActivity.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Activity;

                    use React\Promise\PromiseInterface;
                    use Temporal\Activity\ActivityInterface;
                    use Temporal\Activity\ActivityMethod;

                    #[ActivityInterface]
                    class PaymentActivity
                    {
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:activity', [
            'name' => 'Payment',
        ]);
    }

    public function testGenerateWithAssignedWorker(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Activity/PaymentActivity.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Activity;

                    use React\Promise\PromiseInterface;
                    use Spiral\TemporalBridge\Attribute\AssignWorker;
                    use Temporal\Activity\ActivityInterface;
                    use Temporal\Activity\ActivityMethod;

                    #[ActivityInterface]
                    #[AssignWorker(taskQueue: 'scanner_service')]
                    class PaymentActivity
                    {
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:activity', [
            'name' => 'Payment',
            '--worker' => 'scanner_service',
        ]);
    }

    public function testGenerateWithActivityName(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Activity/PaymentActivity.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Activity;

                    use React\Promise\PromiseInterface;
                    use Temporal\Activity\ActivityInterface;
                    use Temporal\Activity\ActivityMethod;

                    #[ActivityInterface(name: 'payment')]
                    class PaymentActivity
                    {
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:activity', [
            'name' => 'Payment',
            '--activity-name' => 'payment',
        ]);
    }

    public function testGenerateWithMethods(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Activity/PaymentActivity.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Activity;

                    use React\Promise\PromiseInterface;
                    use Temporal\Activity\ActivityInterface;
                    use Temporal\Activity\ActivityMethod;

                    #[ActivityInterface]
                    class PaymentActivity
                    {
                        /**
                         * @return PromiseInterface<mixed>
                         */
                        #[ActivityMethod(name: 'pay')]
                        public function pay(): mixed
                        {
                            // TODO: Implement activity method
                        }

                        /**
                         * @return PromiseInterface<void>
                         */
                        #[ActivityMethod(name: 'refund')]
                        public function refund(): void
                        {
                            // TODO: Implement activity method
                        }

                        /**
                         * @return PromiseInterface<bool>
                         */
                        #[ActivityMethod(name: 'getPaymentStatus')]
                        public function getPaymentStatus(): bool
                        {
                            // TODO: Implement activity method
                        }
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:activity', [
            'name' => 'Payment',
            '--method' => [
                'pay',
                'refund:void',
                'getPaymentStatus:bool',
            ],
        ]);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->files = $this->mockContainer(FilesInterface::class);
    }
}
