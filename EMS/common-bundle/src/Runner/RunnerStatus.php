<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner;

enum RunnerStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Timeout = 'timeout';
    case Unknown = 'unknown';
    case Skipped = 'skipped';
}
