<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use EMS\Helpers\Standard\Json;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpHandleResponse extends AbstractHandleResponse
{
    public function __construct(private readonly ResponseInterface $response, private readonly string $responseContent, string $data = 'Submission send by http.')
    {
        parent::__construct(self::STATUS_SUCCESS, $data);
    }

    public function getHttpResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getHttpResponseContent(): string
    {
        return $this->responseContent;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHttpResponseContentJSON(): array
    {
        if (Json::isEmpty($this->responseContent)) {
            return [];
        }

        return Json::decode($this->responseContent);
    }
}
