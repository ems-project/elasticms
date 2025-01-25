<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Exception;

class NotParsableUrlException extends \Exception
{
    private readonly string $url;

    public function __construct(string $url, private readonly ?string $referer, string $message)
    {
        parent::__construct(\sprintf('Not parsable url %s: %s', $url, $message));
        $this->url = $url;
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
