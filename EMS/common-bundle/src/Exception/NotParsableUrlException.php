<?php

namespace EMS\CommonBundle\Exception;

class NotParsableUrlException extends \Exception
{
    private string $url;
    private ?string $referer;

    public function __construct(string $url, ?string $referer, string $message)
    {
        parent::__construct(\sprintf('Not parsable url %s: %s', $url, $message));
        $this->url = $url;
        $this->referer = $referer;
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
