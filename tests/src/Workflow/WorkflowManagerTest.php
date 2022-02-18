<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Workflow;

use Mockery as m;
use Spiral\TemporalBridge\Tests\App\SimpleWorkflow;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\Workflow\Workflow;
use Spiral\TemporalBridge\Workflow\WorkflowManager;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Internal\Declaration\Prototype\WorkflowPrototype;
use Temporal\Internal\Declaration\Reader\WorkflowReader;

final class WorkflowManagerTest extends TestCase
{
    private WorkflowManager $manager;
    private \Mockery\MockInterface $client;
    private \Mockery\MockInterface $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new WorkflowManager(
            $this->client = m::mock(WorkflowClientInterface::class),
            $this->reader = m::mock(WorkflowReader::class),
        );
    }

    public function testCreatesWorkflowWithoutId(): void
    {
        $class = new \ReflectionClass(SimpleWorkflow::class);

        $this->reader->shouldReceive('fromClass')
            ->with('foo')
            ->andReturn(new WorkflowPrototype('foo', $class->getMethod('handle'), $class));

        $this->assertInstanceOf(Workflow::class, $workflow = $this->manager->create('foo'));
        $this->assertNotEmpty($workflow->getId());
    }

    public function testCreatesWorkflowWithId(): void
    {
        $class = new \ReflectionClass(SimpleWorkflow::class);

        $this->reader->shouldReceive('fromClass')
            ->with('foo')
            ->andReturn(new WorkflowPrototype('foo', $class->getMethod('handle'), $class));

        $this->assertInstanceOf(Workflow::class, $workflow = $this->manager->create('foo', 'foo-id'));
        $this->assertSame('foo-id', $workflow->getId());
    }
}
