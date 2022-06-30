<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Attribute;

use Spiral\Attributes\Factory;
use Spiral\TemporalBridge\Attribute\RegisterWorker;
use Spiral\TemporalBridge\Tests\App\SomeActivity;
use Spiral\TemporalBridge\Tests\App\SomeWorkflow;
use Spiral\TemporalBridge\Tests\App\WithoutAttribute;
use Spiral\TemporalBridge\Tests\TestCase;

final class RegisterWorkerTest extends TestCase
{
    /** @dataProvider registerWorkerDataProvider */
    public function testRegisterWorkerAttribute(\ReflectionClass $class, ?RegisterWorker $expected = null): void
    {
        $reader = (new Factory())->create();

        $this->assertEquals($expected, $reader->firstClassMetadata($class, RegisterWorker::class));
    }

    public function registerWorkerDataProvider(): \Traversable
    {
        yield [new \ReflectionClass(SomeActivity::class), new RegisterWorker('worker1')];
        yield [new \ReflectionClass(SomeWorkflow::class), new RegisterWorker('worker2')];
        yield [new \ReflectionClass(WithoutAttribute::class), null];
    }
}
