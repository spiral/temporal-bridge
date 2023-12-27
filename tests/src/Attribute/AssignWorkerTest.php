<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Attributes\Factory;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Tests\App\SomeActivity;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\Tests\App\WithoutAttribute;
use Spiral\TemporalBridge\Tests\TestCase;

final class AssignWorkerTest extends TestCase
{
    #[DataProvider(methodName: "assignWorkerDataProvider")]
    public function testAssignWorkerAttribute(\ReflectionClass $class, ?AssignWorker $expected = null): void
    {
        $reader = (new Factory())->create();

        $this->assertEquals($expected, $reader->firstClassMetadata($class, AssignWorker::class));
    }

    public static function assignWorkerDataProvider(): \Traversable
    {
        yield [new \ReflectionClass(SomeActivity::class), new AssignWorker('worker1')];
        yield [new \ReflectionClass(SomeWorkflow::class), new AssignWorker('worker2')];
        yield [new \ReflectionClass(WithoutAttribute::class), null];
    }
}
