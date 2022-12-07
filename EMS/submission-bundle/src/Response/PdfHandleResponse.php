<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\CommonBundle\Service\Pdf\PdfOutput;
use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Request\PdfRequest;

final class PdfHandleResponse extends AbstractHandleResponse
{
    public function __construct(private readonly PdfRequest $pdfRequest, private readonly PdfOutput $pdfOutput)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission pdf ready');
    }

    public function getFilename(): string
    {
        return $this->pdfRequest->getPdf()->getFilename();
    }

    public function getContent(): string
    {
        return \base64_encode($this->getContentRaw());
    }

    public function getContentRaw(): string
    {
        return $this->pdfOutput->make();
    }
}
