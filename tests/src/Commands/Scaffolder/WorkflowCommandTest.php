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
final class WorkflowCommandTest extends TestCase
{
    private FilesInterface|MockInterface $files;

    public function testGenerate(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Workflow/PaymentWorkflow.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Workflow;

                    use Temporal\Workflow\WorkflowInterface;
                    use Temporal\Workflow\WorkflowMethod;

                    #[WorkflowInterface]
                    class PaymentWorkflow
                    {
                        /**
                         * Handle workflow
                         */
                        #[WorkflowMethod]
                        public function handle()
                        {
                            // TODO: Implement handle method
                        }
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:workflow', [
            'name' => 'Payment',
        ]);
    }

    public function testGenerateWithWorkflowName(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Workflow/PaymentWorkflow.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Workflow;

                    use Temporal\Workflow\WorkflowInterface;
                    use Temporal\Workflow\WorkflowMethod;

                    #[WorkflowInterface]
                    class PaymentWorkflow
                    {
                        /**
                         * Handle workflow
                         */
                        #[WorkflowMethod(name: 'PaymentWorkflow')]
                        public function handle()
                        {
                            // TODO: Implement handle method
                        }
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:workflow', [
            'name' => 'Payment',
            '--workflow-name' => 'PaymentWorkflow',
        ]);
    }

    public function testGenerateWithWorker(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Workflow/PaymentWorkflow.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Workflow;

                    use Spiral\TemporalBridge\Attribute\AssignWorker;
                    use Temporal\Workflow\WorkflowInterface;
                    use Temporal\Workflow\WorkflowMethod;

                    #[WorkflowInterface]
                    #[AssignWorker(taskQueue: 'test')]
                    class PaymentWorkflow
                    {
                        /**
                         * Handle workflow
                         */
                        #[WorkflowMethod]
                        public function handle()
                        {
                            // TODO: Implement handle method
                        }
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:workflow', [
            'name' => 'Payment',
            '--worker' => 'test',
        ]);
    }

    public function testGenerateWithQueryMethods(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Workflow/PaymentWorkflow.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Workflow;

                    use Temporal\Workflow\QueryMethod;
                    use Temporal\Workflow\WorkflowInterface;
                    use Temporal\Workflow\WorkflowMethod;

                    #[WorkflowInterface]
                    class PaymentWorkflow
                    {
                        /**
                         * Handle workflow
                         */
                        #[WorkflowMethod]
                        public function handle()
                        {
                            // TODO: Implement handle method
                        }

                        #[QueryMethod]
                        public function getPayment(): string
                        {
                            // TODO: Implement query method
                        }

                        #[QueryMethod]
                        public function getTotal(): int
                        {
                            // TODO: Implement query method
                        }

                        #[QueryMethod]
                        public function getLastTransaction(): mixed
                        {
                            // TODO: Implement query method
                        }
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:workflow', [
            'name' => 'Payment',
            '--query-method' => [
                'getPayment:string',
                'getTotal:int',
                'getLastTransaction',
            ],
        ]);
    }

    public function testGenerateWithSignalMethods(): void
    {
        $this->files->shouldReceive('exists')->andReturnFalse();
        $this->files->shouldReceive('write')->once()->withArgs(static function (string $path, string $body) {
            Assert::true(\str_ends_with($path, '/app/src/Endpoint/Temporal/Workflow/PaymentWorkflow.php'));
            Assert::same($body, <<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Spiral\TemporalBridge\Tests\App\Endpoint\Temporal\Workflow;

                    use Temporal\Workflow\SignalMethod;
                    use Temporal\Workflow\WorkflowInterface;
                    use Temporal\Workflow\WorkflowMethod;

                    #[WorkflowInterface]
                    class PaymentWorkflow
                    {
                        /**
                         * Handle workflow
                         */
                        #[WorkflowMethod]
                        public function handle()
                        {
                            // TODO: Implement handle method
                        }

                        #[SignalMethod]
                        public function pay(): void
                        {
                            // TODO: Implement signal method
                        }

                        #[SignalMethod]
                        public function cancel(): void
                        {
                            // TODO: Implement signal method
                        }
                    }

                    PHP);
            return true;
        });

        $this->runCommand('create:workflow', [
            'name' => 'Payment',
            '--signal-method' => [
                'pay',
                'cancel',
            ],
        ]);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->files = $this->mockContainer(FilesInterface::class);
    }
}
