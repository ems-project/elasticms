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

    /**
     * @return array<mixed>
     */
    public function v1Responses(): array
    {
        $response = $this->httpClient->request('POST', self::BASE_URL.'/v1/responses', [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => 'gpt-4.1-nano',
                'input' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a French translator',
                    ],
                    [
                        'role' => 'user',
                        'content' => Json::encode([
                            'Dutch' => [
                                'page_title_nl' => 'Nieuws',
                                'page_description_nl' => 'Vandaag is er niets gebeurd!',
                            ],
                        ]),
                    ],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'translation',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'French' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'page_title_fr' => ['type' => 'string'],
                                        'page_description_fr' => ['type' => 'string'],
                                    ],
                                    'required' => ['page_title_fr', 'page_description_fr'],
                                    'additionalProperties' => false,
                                ],
                            ],
                            'required' => ['French'],
                            'additionalProperties' => false,
                        ],
                        'strict' => true,
                    ],
                ],
            ],
        ]);

        return $response->toArray();
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
