<?php

declare(strict_types=1);

namespace App\CLI\Client\HttpClient;

use App\CLI\Client\WebToElasticms\Helper\Url;

class UrlReport
{
    private Url $url;
    private int $statusCode;
    private ?string $message;

    public function __construct(Url $url, int $statusCode, ?string $message = null)
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
        $this->message = $message;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function isValid(): bool
    {
        return null === $this->message;
    }
}
