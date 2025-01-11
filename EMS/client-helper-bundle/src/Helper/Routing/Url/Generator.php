<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing\Url;

use Symfony\Component\Routing\RouterInterface;

final class Generator
{
    private string $baseUrl = '';
    private string $phpApp = '';

    /**
     * Regex for getting the base URL without the phpApp
     * So we can relative link to other applications.
     */
    private const string REGEX_BASE_URL = '/^(?P<baseUrl>\/.*?)(?:(?P<phpApp>\/[\-_A-Za-z0-9]*.php)|\/|)$/i';

    public function __construct(RouterInterface $router)
    {
        \preg_match(self::REGEX_BASE_URL, $router->getContext()->getBaseUrl(), $match);

        if (isset($match['baseUrl'])) {
            $this->baseUrl = $match['baseUrl'];
        }

        if (isset($match['phpApp'])) {
            $this->phpApp = $match['phpApp'];
        }
    }

    public function createUrl(string $relativePath, string $path): string
    {
        return $this->baseUrl.$relativePath.$this->phpApp.$path;
    }

    public function prependBaseUrl(string $url): string
    {
        $url = \trim($url);

        if (!\str_starts_with($url, '/')) {
            return $url;
        }

        $baseUrl = $this->baseUrl.$this->phpApp;

        if (\strlen($baseUrl) > 0 && \str_starts_with($url, $baseUrl)) {
            return $url;
        }

        return $baseUrl.$url;
    }
}
