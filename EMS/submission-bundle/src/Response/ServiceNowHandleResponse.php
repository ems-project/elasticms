<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\Helpers\Standard\Json;

final class ServiceNowHandleResponse extends AbstractHandleResponse
{
    public function __construct(string $json)
    {
        parent::__construct($this->deriveStatus($json), $json);
    }

    public function getResultProperty(string $property): string
    {
        try {
            $decodedData = Json::decode($this->data);
        } catch (\Throwable) {
            return '';
        }

        if (isset($decodedData['result'][$property])) {
            return (string) $decodedData['result'][$property];
        }

        return '';
    }

    private function deriveStatus(string $json): string
    {
        try {
            $data = Json::decode($json);
        } catch (\Throwable) {
            return self::STATUS_ERROR;
        }

        if (isset($data['status']) && 'failure' === $data['status']) {
            return self::STATUS_ERROR;
        }

        return self::STATUS_SUCCESS;
    }
}
