<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests;

use Spiral\TemporalBridge\Generator\Utils;

final class UtilsTest extends TestCase
{
    public function testParseParameters(): void
    {
        $parameters = [
            'userId:string',
            'name',
            'exit:bool',
        ];

        $parsed = Utils::parseParameters($parameters);

        $this->assertSame('userId', $parsed['userId']->getName());
        $this->assertSame('string', $parsed['userId']->getType());

        $this->assertSame('name', $parsed['name']->getName());
        $this->assertSame('string', $parsed['name']->getType());

        $this->assertSame('exit', $parsed['exit']->getName());
        $this->assertSame('bool', $parsed['exit']->getType());
    }

    public function testParseMethods(): void
    {
        $methods = [
            'exit',
            'handler:string',
            'book:bool,name:string,exit:bool',
        ];

        $parsed = Utils::parseMethods($methods);

        $this->assertSame('exit', $parsed['exit']->getName());
        $this->assertSame('void', $parsed['exit']->getReturnType());
        $this->assertSame([], $parsed['exit']->getParameters());

        $this->assertSame('handler', $parsed['handler']->getName());
        $this->assertSame('string', $parsed['handler']->getReturnType());
        $this->assertSame([], $parsed['handler']->getParameters());

        $this->assertSame('book', $parsed['book']->getName());
        $this->assertSame('bool', $parsed['book']->getReturnType());
        $this->assertCount(2, $parsed['book']->getParameters());

        $this->assertSame('name', $parsed['book']->getParameters()['name']->getName());
        $this->assertSame('string', $parsed['book']->getParameters()['name']->getType());

        $this->assertSame('exit', $parsed['book']->getParameters()['exit']->getName());
        $this->assertSame('bool', $parsed['book']->getParameters()['exit']->getType());
    }
}
