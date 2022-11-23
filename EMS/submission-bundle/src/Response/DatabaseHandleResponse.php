<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Entity\FormSubmission;

final class DatabaseHandleResponse extends AbstractHandleResponse
{
    private FormSubmission $formSubmission;

    public function __construct(FormSubmission $formSubmission)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission saved database.');

        $this->formSubmission = $formSubmission;
    }

    public function getFormSubmission(): FormSubmission
    {
        return $this->formSubmission;
    }
}
