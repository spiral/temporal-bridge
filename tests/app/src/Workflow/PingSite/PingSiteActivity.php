<?php

declare(strict_types=1);

namespace App\Workflow\PingSite;

use Psr\Log\LoggerInterface;
use Spiral\TemporalBridge\Attribute\AssignWorker;

class PingSiteActivity implements PingSiteActivityInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function handle(string $name): mixed
    {
        $this->logger->info('Something special happens here.', func_get_args());

        yield 'Success';
    }
}
