<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Config;

use Spiral\Core\InjectableConfig;

final class SkeletonConfig extends InjectableConfig
{
    public const CONFIG = 'skeleton';
    protected $config = [];
}
