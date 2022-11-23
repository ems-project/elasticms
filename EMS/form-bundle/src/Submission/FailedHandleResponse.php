<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

class FailedHandleResponse extends AbstractHandleResponse
{
    public function __construct(string $data)
    {
        parent::__construct(self::STATUS_ERROR, $data);
    }
}
