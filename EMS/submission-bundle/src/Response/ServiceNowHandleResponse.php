<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;

final class ServiceNowHandleResponse extends AbstractHandleResponse
{
    public function __construct(string $json)
    {
        parent::__construct($this->deriveStatus($json), $json);
    }

    public function getResultProperty(string $property): string
    {
        $decodedData = \json_decode($this->data, true);
        if (JSON_ERROR_NONE !== \json_last_error()) {
            return '';
        }

        if (isset($decodedData['result'][$property])) {
            return (string) $decodedData['result'][$property];
        }

        return '';
    }

    private function deriveStatus(string $json): string
    {
        $data = \json_decode($json, true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            return self::STATUS_ERROR;
        }

        if (isset($data['status']) && 'failure' === $data['status']) {
            return self::STATUS_ERROR;
        }

        return self::STATUS_SUCCESS;
    }
}
