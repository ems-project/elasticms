<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Request\ZipRequest;

final class ZipHandleResponse extends AbstractHandleResponse
{
    public function __construct(private readonly ZipRequest $zipRequest, private readonly string $content)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission zip ready.');
    }

    public function getContent(): string
    {
        return \base64_encode($this->getContentRaw());
    }

    public function getContentRaw(): string
    {
        return $this->content;
    }

    public function getFilename(): string
    {
        return $this->zipRequest->getFilename();
    }

    public function getZipRequest(): ZipRequest
    {
        return $this->zipRequest;
    }
}
