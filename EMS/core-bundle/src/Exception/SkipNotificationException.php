<?php

namespace EMS\CoreBundle\Exception;

class SkipNotificationException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
