<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use Symfony\Component\Mime\Email;

final class EmailHandleResponse extends AbstractHandleResponse
{
    private Email $message;

    public function __construct(Email $message)
    {
        $this->message = $message;

        parent::__construct(self::STATUS_SUCCESS, 'Submission send by mail.');
    }

    public function getMessage(): Email
    {
        return $this->message;
    }
}
