<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Workflow;

use Mockery as m;
use Spiral\TemporalBridge\Exception\WorkflowNotFoundException;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\Workflow\RunningWorkflow;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Client\WorkflowStubInterface;
use Temporal\DataConverter\EncodedValues;

final class RunningWorkflowTest extends TestCase
{
    /** @var m\MockInterface|WorkflowClientInterface */
    private WorkflowClientInterface $client;
    /** @var m\MockInterface|WorkflowStubInterface */
    private WorkflowStubInterface $workflowStub;
    private RunningWorkflow $workflow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = m::mock(WorkflowClientInterface::class);
        $this->workflowStub = m::mock(WorkflowStubInterface::class);

        $this->workflow = new RunningWorkflow(
            $this->client,
            $this->workflowStub,
            'foo-id',
            'some-class'
        );
    }

    public function testProxyMethods(): void
    {
        $this->workflowStub->shouldReceive('query')
            ->once()
            ->with('foo', 'bar')
            ->andReturn($values = EncodedValues::empty());

        $this->assertSame($values, $this->workflow->query('foo', 'bar'));
    }

    public function testGetsWorkflowObject(): void
    {
        $this->client->shouldReceive('newRunningWorkflowStub')
            ->once()
            ->with('some-class', 'foo-id')
            ->andReturn($object = new \stdClass());

        $this->assertSame($object, $this->workflow->getWorkflow());
    }

    public function testGetsWorkflowObjectWithPassingClass(): void
    {
        $this->client->shouldReceive('newRunningWorkflowStub')
            ->once()
            ->with('another-class', 'foo-id')
            ->andReturn($object = new \stdClass());

        $this->assertSame($object, $this->workflow->getWorkflow('another-class'));
    }

    public function testGetsWorkflowObjectWithoutClass(): void
    {
        $this->expectException(WorkflowNotFoundException::class);

        $workflow = new RunningWorkflow(
            $this->client,
            $this->workflowStub,
            'foo-id',
            null
        );

        $workflow->getWorkflow();
    }
}
