<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use Symfony\Component\Mime\Email;

final class EmailHandleResponse extends AbstractHandleResponse
{
    public function __construct(private readonly Email $message)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission send by mail.');
    }

    public function getMessage(): Email
    {
        return $this->message;
    }
}
