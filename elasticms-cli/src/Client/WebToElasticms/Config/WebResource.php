<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Config;

class WebResource
{
    private string $url;
    private string $locale;
    private string $type;

    public function __construct(string $url, string $locale, string $type)
    {
        $this->url = $url;
        $this->locale = $locale;
        $this->type = $type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
