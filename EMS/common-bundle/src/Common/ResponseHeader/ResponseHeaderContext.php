<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\ResponseHeader;

use EMS\Helpers\Html\Headers;

final class ResponseHeaderContext
{
    /** @var array<string, string> */
    private array $headers = [
        Headers::X_CONTENT_TYPE_OPTIONS => Headers::X_CONTENT_TYPE_OPTIONS_NOSNIFF,
        Headers::REFERRER_POLICY => Headers::REFERRER_POLICY_STRICT_ORIGIN_WHEN_CROSS_ORIGIN,
        Headers::PERMISSIONS_POLICY => 'camera=(), microphone=(), geolocation=()',
    ];

    /** @var array<string, list<string>> */
    private array $cspSources = [];

    private ?string $nonce = null;

    /**
     * @var array<string, list<string>>
     */
    private const array DEFAULT_CSP_SOURCES = [
        'default-src' => ["'self'"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'frame-ancestors' => ["'none'"],
    ];

    public function setHeader(string $name, string $value): void
    {
        $name = Headers::normalizeName($name);

        if (!Headers::validateValue($value)) {
            throw new \InvalidArgumentException(\sprintf('Invalid value for HTTP header "%s".', $name));
        }

        if (\in_array($name, [
            Headers::CONTENT_SECURITY_POLICY,
            Headers::CONTENT_SECURITY_POLICY_REPORT_ONLY,
        ], true)) {
            throw new \InvalidArgumentException('Use addCspSource() to configure the Content Security Policy.');
        }

        $this->headers[$name] = $value;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function addCspSource(string $directive, string $source): void
    {
        $directive = \strtolower($directive);

        if (1 !== \preg_match('/^[a-z][a-z0-9-]*$/D', $directive)) {
            throw new \InvalidArgumentException(\sprintf('Invalid CSP directive "%s".', $directive));
        }

        if ('' === $source || 1 === \preg_match('/[\x00-\x20\x7F;,]/', $source)) {
            throw new \InvalidArgumentException(\sprintf('Invalid CSP source "%s".', $source));
        }

        $sources = $this->cspSources[$directive] ?? [];

        if (\in_array($source, $sources, true)) {
            return;
        }

        if ([] !== $sources && (
            "'none'" === $source
                || \in_array("'none'", $sources, true)
        )) {
            throw new \InvalidArgumentException(\sprintf('CSP directive "%s" cannot combine \'none\' with other sources.', $directive));
        }

        $sources[] = $source;
        $this->cspSources[$directive] = $sources;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getCspSources(): array
    {
        return $this->cspSources;
    }

    public function getCspHeader(): string
    {
        $sources = self::DEFAULT_CSP_SOURCES;

        foreach ($this->cspSources as $directive => $directiveSources) {
            $sources[$directive] = $directiveSources;
        }

        $directives = [];

        foreach ($sources as $directive => $directiveSources) {
            $directives[] = $directive.' '.\implode(' ', $directiveSources);
        }

        return \implode('; ', $directives);
    }

    public function getNonce(): string
    {
        return $this->nonce ??= \base64_encode(\random_bytes(32));
    }

    public function hasNonce(): bool
    {
        return null !== $this->nonce;
    }
}
