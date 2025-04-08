<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Exception;

class NotParsableUrlException extends \Exception
{
    public function __construct(private readonly string $url, private readonly ?string $referer, string $message)
    {
        parent::__construct(\sprintf('Not parsable url %s: %s', $url, $message));
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }
}
