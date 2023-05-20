<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Exception;

class SkipSubmissionException extends \Exception
{
    public function __construct()
    {
        parent::__construct('This submission does not have to be handled');
    }
}
