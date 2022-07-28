<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Generator;

use PHPUnit\Framework\TestCase;

final class GeneratorAssertions
{
    private string $filename;
    private string $data;

    public function withFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function withData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function assertFilename(string $filename): self
    {
        TestCase::assertSame(
            $this->filename,
            $filename,
            \sprintf('Generated filename [%s] has different name than. [%s] given.', $this->filename, $filename)
        );

        return $this;
    }

    public function assertCodeContains(string $string): self
    {
        TestCase::assertStringContainsString(
            $string,
            $this->data,
            \sprintf('Generated PHP code doesn\'t contain string [%s]. PHP code: %s', $string, $this->data)
        );

        return $this;
    }

    public function assertCodeNotContains(string $string): self
    {
        TestCase::assertStringNotContainsString(
            $string,
            $this->data,
            \sprintf('Generated PHP code contains string [%s]. PHP code: %s', $string, $this->data)
        );

        return $this;
    }

    public function dump(): self
    {
        var_dump($this->data);
    }
}
