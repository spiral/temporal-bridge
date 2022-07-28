<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Tests\TestCase;

final class ContextTest extends TestCase
{
    private Context $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new Context('src/app/', 'App\\Foo', 'Bar');
    }

    public function testWithCronSchedule(): void
    {
        $this->assertFalse($this->context->isScheduled());

        $this->assertTrue($this->context->withCronSchedule()->isScheduled());
    }

    public function testWithActivity(): void
    {
        $this->assertFalse($this->context->hasActivity());

        $this->assertTrue($this->context->withActivity()->hasActivity());
    }

    public function testWithHandler(): void
    {
        $this->assertFalse($this->context->hasHandler());

        $this->assertTrue($this->context->withHandler()->hasHandler());
    }

    public function testGetsNamespace(): void
    {
        $this->assertSame(
            'App\\Foo',
            $this->context->getNamespace()
        );
    }

    public function testGetsPath(): void
    {
        $this->assertSame(
            'src/app/',
            $this->context->getPath()
        );
    }

    public function testGetsBaseClass(): void
    {
        $this->assertSame(
            'Bar',
            $this->context->getBaseClass()
        );

        $this->assertSame(
            'BarBaz',
            $this->context->getBaseClass('Baz')
        );
    }

    public function testGetsClassPath(): void
    {
        $this->assertSame(
            'src/app/Bar.php',
            $this->context->getClassPath()
        );
    }

    public function testGetsBaseClassInterface(): void
    {
        $this->assertSame(
            'BarInterface',
            $this->context->getBaseClassInterface()
        );

        $this->assertSame(
            'BarBazInterface',
            $this->context->getBaseClassInterface('Baz')
        );
    }

    public function testGetsBaseClassInterfaceWithNamespace(): void
    {
        $this->assertSame(
            'App\Foo\BarInterface',
            $this->context->getBaseClassInterfaceWithNamespace()
        );

        $this->assertSame(
            'App\Foo\BarBazInterface',
            $this->context->getBaseClassInterfaceWithNamespace('Baz')
        );
    }

    public function testGetsClass(): void
    {
        $this->assertSame(
            'Bar',
            $this->context->getClass()
        );

        $this->assertSame(
            'BarBaz',
            $this->context->withClassPostfix('Baz')->getClass()
        );

        $this->assertSame(
            'Bar',
            $this->context->withClassPostfix('Bar')->getClass()
        );

        $this->assertSame(
            'BarBazFoo',
            $this->context->withClassPostfix('Baz')->getClass('Foo')
        );
    }

    public function testGetsClassInterface(): void
    {
        $this->assertSame(
            'BarInterface',
            $this->context->getClassInterface()
        );

        $this->assertSame(
            'BarBazInterface',
            $this->context->withClassPostfix('Baz')->getClassInterface()
        );

        $this->assertSame(
            'BarInterface',
            $this->context->withClassPostfix('Bar')->getClassInterface()
        );

        $this->assertSame(
            'BarBazFooInterface',
            $this->context->withClassPostfix('Baz')->getClassInterface('Foo')
        );
    }

    public function testGetsClassInterfaceWithNamespace(): void
    {
        $this->assertSame(
            'App\Foo\BarInterface',
            $this->context->getClassInterfaceWithNamespace()
        );

        $this->assertSame(
            'App\Foo\BarBazInterface',
            $this->context->withClassPostfix('Baz')->getClassInterfaceWithNamespace()
        );

        $this->assertSame(
            'App\Foo\BarInterface',
            $this->context->withClassPostfix('Bar')->getClassInterfaceWithNamespace()
        );

        $this->assertSame(
            'App\Foo\BarBazFooInterface',
            $this->context->withClassPostfix('Baz')->getClassInterfaceWithNamespace('Foo')
        );
    }

    public function testGetsClassWithNamespace(): void
    {
        $this->assertSame(
            'App\Foo\Bar',
            $this->context->getClassWithNamespace()
        );

        $this->assertSame(
            'App\Foo\BarFoo',
            $this->context->getClassWithNamespace('Foo')
        );

        $this->assertSame(
            'App\Foo\BarBazFoo',
            $this->context->withClassPostfix('Baz')->getClassWithNamespace('Foo')
        );
    }

    public function testGetsHandlerMethodName(): void
    {
        $this->assertSame(
            'handle',
            $this->context->getHandlerMethodName()
        );

        $this->assertFalse(
            $this->context->isHandlerMethodNameChanged()
        );

        $this->assertSame(
            'foo',
            $this->context->withHandlerMethod('foo')->getHandlerMethodName()
        );

        $this->assertTrue(
            $this->context->isHandlerMethodNameChanged()
        );
    }

    public function testGetsHandlerMethod(): void
    {
        $method = $this->context->getHandlerMethod();

        $this->assertTrue($method->isPublic());

        $this->assertSame(
            'mixed',
            $method->getReturnType()
        );

        $this->assertSame(
            'handle',
            $method->getName()
        );

        $this->assertSame(
            'foo',
            $this->context->withHandlerMethod('foo')->getHandlerMethod()->getName()
        );
    }
}
