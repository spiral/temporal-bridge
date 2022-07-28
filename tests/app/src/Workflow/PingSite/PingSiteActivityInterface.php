<?php

declare(strict_types=1);

namespace App\Workflow\PingSite;

use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface(prefix: 'PingSite.')]
interface PingSiteActivityInterface
{
    #[ActivityMethod]
    public function handle(string $name): mixed;
}
