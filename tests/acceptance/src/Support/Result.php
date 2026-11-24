<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Tests\Acceptance\Support;

/**
 * Outcome of a finished process.
 */
final class Result
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $stdout,
        public readonly string $stderr,
    ) {}

    /**
     * Both streams together: errors land on either of them depending on who reports them.
     */
    public function output(): string
    {
        return $this->stdout . $this->stderr;
    }

    public function __toString(): string
    {
        return \sprintf("Exit code: %d\n--- stdout ---\n%s\n--- stderr ---\n%s", $this->exitCode, $this->stdout, $this->stderr);
    }
}
