<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Exception;

class TransientException extends \Exception
{
    public function __construct(\Exception $previous)
    {
        parent::__construct('Transient exception', 500, $previous);
    }
}
