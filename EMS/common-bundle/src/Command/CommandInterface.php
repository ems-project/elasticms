<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

interface CommandInterface
{
    public const EXECUTE_SUCCESS = 0;
    public const EXECUTE_ERROR = 1;
}
