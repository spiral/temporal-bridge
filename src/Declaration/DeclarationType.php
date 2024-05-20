<?php

declare(strict_types=1);

namespace Spiral\TemporalBridge\Declaration;

enum DeclarationType: string
{
    case Workflow = 'workflow';
    case Activity = 'activity';
}
