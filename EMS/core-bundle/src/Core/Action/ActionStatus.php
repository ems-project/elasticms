<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Action;

enum ActionStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
    case FAILED = 'failed';
}
