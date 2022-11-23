<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Request\SftpRequest;

final class SftpHandleResponse extends AbstractHandleResponse
{
    /** @var SftpRequest */
    private $sftpRequest;

    /** @var array<array{path: string, contents: string}> */
    private $transportedFiles;

    /**
     * @param array<array{path: string, contents: string}> $transportedFiles
     */
    public function __construct(SftpRequest $sftpRequest, array $transportedFiles)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission send by sftp.');

        $this->sftpRequest = $sftpRequest;
        $this->transportedFiles = $transportedFiles;
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
