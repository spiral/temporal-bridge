<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use Nette\PhpGenerator\PhpNamespace;
use Spiral\Files\FilesInterface;
use Spiral\TemporalBridge\Generator\Context;
use Spiral\TemporalBridge\Generator\PhpCodePrinter;
use Spiral\TemporalBridge\Tests\TestCase;

final class PhpCodePrinterTest extends TestCase
{
    public function testPrint(): void
    {
        $printer = new PhpCodePrinter(
            new PhpNamespace('Foo\\Bar'),
            new Context('src/app/', 'App//Foo', 'Bar')
        );

        $files = $this->mockContainer(FilesInterface::class);

        $files->shouldReceive('write')->once()->withSomeOfArgs('src/app/Bar.php', <<<CODE
<?php

declare(strict_types=1);

namespace Foo\Bar;


CODE
, null, true);

        $printer->print($files);
    }
}
