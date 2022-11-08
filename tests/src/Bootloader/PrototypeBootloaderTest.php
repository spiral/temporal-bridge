<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Bootloader;

use Spiral\Prototype\PrototypeRegistry;
use Spiral\TemporalBridge\Tests\TestCase;
use Spiral\TemporalBridge\WorkflowManagerInterface;
use Temporal\Client\WorkflowClientInterface;

class PrototypeBootloaderTest extends TestCase
{
    /** @dataProvider propertiesDataProvider */
    public function testBindProperties(string $expected, string $property): void
    {
        $registry = $this->getContainer()->get(PrototypeRegistry::class);

        $this->assertInstanceOf(
            $expected,
            $this->getContainer()->get($registry->resolveProperty($property)->type->name())
        );
    }

    public function propertiesDataProvider(): \Traversable
    {
        yield [WorkflowClientInterface::class, 'workflow'];
        yield [WorkflowManagerInterface::class, 'workflow-manager'];
    }
}
