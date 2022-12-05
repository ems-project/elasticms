<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Request\SftpRequest;

final class SftpHandleResponse extends AbstractHandleResponse
{
    /**
     * @param array<array{path: string, contents: string}> $transportedFiles
     */
    public function __construct(private readonly SftpRequest $sftpRequest, private readonly array $transportedFiles)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission send by sftp.');
    }

    public function getSftpRequest(): SftpRequest
    {
        return $this->sftpRequest;
    }

    /**
     * @return array<array{path: string, contents: string}>
     */
    public function getTransportedFiles(): array
    {
        return $this->transportedFiles;
    }
}
