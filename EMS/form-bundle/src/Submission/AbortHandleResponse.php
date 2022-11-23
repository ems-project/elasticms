<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

final class AbortHandleResponse extends AbstractHandleResponse
{
    public function __construct(string $data)
    {
        parent::__construct(self::STATUS_ERROR, $data);
    }
}
