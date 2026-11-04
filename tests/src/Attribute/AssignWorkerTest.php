<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Attribute;

use Spiral\Attributes\Factory;
use Spiral\TemporalBridge\Attribute\AssignWorker;
use Spiral\TemporalBridge\Tests\App\SomeActivity;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\Tests\App\WithoutAttribute;
use Spiral\TemporalBridge\Tests\TestCase;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class AssignWorkerTest extends TestCase
{
    public static function assignWorkerDataProvider(): \Traversable
    {
        yield [new \ReflectionClass(SomeActivity::class), new AssignWorker('worker1')];
        yield [new \ReflectionClass(SomeWorkflow::class), new AssignWorker('worker2')];
        yield [new \ReflectionClass(WithoutAttribute::class), null];
    }

    #[DataProvider("assignWorkerDataProvider")]
    public function testAssignWorkerAttribute(\ReflectionClass $class, ?AssignWorker $expected = null): void
    {
        $reader = (new Factory())->create();

        Assert::equals($reader->firstClassMetadata($class, AssignWorker::class), $expected);
    }
}
