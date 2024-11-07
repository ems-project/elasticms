<?php

declare(strict_types=1);

namespace App\CLI\Client\HttpClient;

use EMS\CommonBundle\Helper\Url;

class UrlReport
{
    public function __construct(private readonly Url $url, private readonly int $statusCode, private readonly ?string $message = null)
    {
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
