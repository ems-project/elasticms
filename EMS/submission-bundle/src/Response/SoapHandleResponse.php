<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\SubmissionBundle\Request\SoapRequest;

final class SoapHandleResponse extends AbstractHandleResponse
{
    private SoapRequest $soapRequest;
    /** @var mixed */
    private $soapResponse;

    /**
     * @param mixed $soapResponse
     */
    public function __construct(SoapRequest $sftpRequest, $soapResponse)
    {
        parent::__construct(self::STATUS_SUCCESS, 'Submission send by soap.');

        $this->soapRequest = $sftpRequest;
        $this->soapResponse = $soapResponse;
    }

    public function getSoapRequest(): SoapRequest
    {
        return $this->soapRequest;
    }

    /**
     * @return mixed
     */
    public function getSoapResponse()
    {
        return $this->soapResponse;
    }
}
