<?php

declare(strict_types=1);

namespace EMS\CommonBundle;

class Commands
{
    final public const CURL = 'ems:curl';
    final public const STATUS = 'ems:status';
    final public const CLEAR_LOGS = 'ems:logs:clear';
    final public const ADMIN_COMMAND = 'ems:admin:command';
    final public const ADMIN_NEXT_JOB = 'ems:admin:next-job';
}
