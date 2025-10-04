<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Ai;

use EMS\Helpers\Standard\Json;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OpenAiResponse
{
    /** @var array<mixed> */
    private array $data;

    public function __construct(ResponseInterface $response)
    {
        $this->data = $response->toArray();
    }

    /** @return array<mixed> */
    public function toArray(): array
    {
        $text = $this->data['output'][0]['content'][0]['text'] ?? null;

        if (null === $text) {
            throw new \RuntimeException('could not parse output');
        }

        return Json::isJson($text) ? Json::decode($text) : ['text' => $text];
    }
}
