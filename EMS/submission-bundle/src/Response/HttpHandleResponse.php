<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Response;

use EMS\FormBundle\Submission\AbstractHandleResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpHandleResponse extends AbstractHandleResponse
{
    /** @var ResponseInterface */
    private $response;
    /** @var string */
    private $responseContent;

    public function __construct(ResponseInterface $response, string $responseContent, string $data = 'Submission send by http.')
    {
        $this->response = $response;
        $this->responseContent = $responseContent;

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
        return \json_decode($this->responseContent, true) ?? [];
    }
}
