<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\SubmissionBundle\Exception\SkipSubmissionException;

class FailedHandleResponse extends AbstractHandleResponse
{
    public function __construct(\Throwable $exception)
    {
        $previous = $exception->getPrevious();
        if ($previous instanceof SkipSubmissionException) {
            throw $previous;
        }
        parent::__construct(self::STATUS_ERROR, \sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
    }
}
