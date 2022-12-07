<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Entity\FormSubmission;

final class DatabaseHandleResponse extends AbstractHandleResponse
{
    public function __construct(private readonly FormSubmission $formSubmission)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission saved database.');
    }

    public function getFormSubmission(): FormSubmission
    {
        return $this->formSubmission;
    }
}
