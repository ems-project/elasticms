<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Ai;

use EMS\CommonBundle\Common\KeyStore;
use EMS\Helpers\Standard\Json;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiService
{
    private const string KEY_STORE = 'openai';
    private const string BASE_URL = 'https://api.openai.com';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly KeyStore $keyStore
    ) {
    }

    public function v1Responses(OpenAiRequest $request): OpenAiResponse
    {
        $response = $this->httpClient->request(
            method: 'POST',
            url: self::BASE_URL.'/v1/responses',
            options: [
                'headers' => $this->getHeaders(),
                'json' => $request->body,
            ]
        );
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $body = $response->getContent(false);
            throw new \RuntimeException(\sprintf('Open AI - error (%d): %s', $statusCode, $body));
        }

        return new OpenAiResponse($response);
    }

    /** @return array{ 'Content-Type': 'application/json', 'Authorization': string } */
    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->keyStore->get(self::KEY_STORE),
        ];
    }
}
