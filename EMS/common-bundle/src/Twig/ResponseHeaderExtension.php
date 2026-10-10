<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderManager;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class ResponseHeaderExtension implements RuntimeExtensionInterface
{
    public function __construct(
        private ResponseHeaderManager $responseHeaderManager,
    ) {
    }

    #[AsTwigFunction(name: 'ems_http_header')]
    public function httpHeader(string $name, string $value): void
    {
        $this->responseHeaderManager
            ->getContext()
            ->setHeader($name, $value);
    }

    #[AsTwigFunction(name: 'ems_remove_http_header')]
    public function removeHttpHeader(string $name): void
    {
        $this->responseHeaderManager
            ->getContext()
            ->removeHeader($name);
    }

    #[AsTwigFunction(name: 'ems_clear_http_headers')]
    public function clearHttpHeaders(): void
    {
        $this->responseHeaderManager
            ->getContext()
            ->clearHeaders();
    }

    #[AsTwigFunction(name: 'ems_csp_source')]
    public function cspSource(string $directive, string $source): void
    {
        $this->responseHeaderManager
            ->getContext()
            ->addCspSource($directive, $source);
    }

    #[AsTwigFunction(name: 'ems_nonce')]
    public function nonce(string $directive = 'script-src'): string
    {
        if (!\in_array($directive, [
            'script-src',
            'script-src-elem',
            'style-src',
            'style-src-elem',
        ], true)) {
            throw new \InvalidArgumentException(\sprintf('The CSP directive "%s" does not support element nonces.', $directive));
        }

        $context = $this->responseHeaderManager->getContext();
        $nonce = $context->getNonce();

        $context->addCspSource($directive, \sprintf("'nonce-%s'", $nonce));

        return $nonce;
    }

    /**
     * @param array<string, string|bool|null> $attributes
     */
    #[AsTwigFilter(name: 'ems_csp_script', isSafe: ['html'])]
    public function cspScriptHash(string $content, array $attributes = []): string
    {
        return $this->renderCspTag('script', 'script-src', $content, $attributes);
    }

    /**
     * @param array<string, string|bool|null> $attributes
     */
    #[AsTwigFilter(name: 'ems_csp_script', isSafe: ['html'])]
    public function cspStyleHash(string $content, array $attributes = []): string
    {
        return $this->renderCspTag('style', 'style-src', $content, $attributes);
    }

    /**
     * @param array<string, string|bool|null> $attributes
     */
    private function renderCspTag(
        string $tag,
        string $directive,
        string $content,
        array $attributes,
    ): string {
        $content = \str_replace(["\r\n", "\r"], "\n", $content);

        if (false !== \stripos($content, '</'.$tag)) {
            throw new \InvalidArgumentException(\sprintf('The content must not contain a closing "%s" tag.', $tag));
        }

        $htmlAttributes = '';

        foreach ($attributes as $name => $value) {
            if (!\is_string($name)
                || 1 !== \preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/D', $name)
            ) {
                throw new \InvalidArgumentException('Invalid HTML attribute name.');
            }

            $normalizedName = \strtolower($name);

            if (\str_starts_with($normalizedName, 'on')
                || \in_array($normalizedName, ['src', 'nonce', 'integrity'], true)
            ) {
                throw new \InvalidArgumentException(\sprintf('Attribute "%s" is not supported by this filter.', $name));
            }

            if (null === $value || false === $value) {
                continue;
            }

            if (true === $value) {
                $htmlAttributes .= ' '.$name;

                continue;
            }

            $htmlAttributes .= \sprintf(
                ' %s="%s"',
                $name,
                \htmlspecialchars(
                    $value,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8',
                ),
            );
        }

        $source = "'sha256-"
            .\base64_encode(\hash('sha256', $content, true))
            ."'";

        $this->responseHeaderManager
            ->getContext()
            ->addCspSource($directive, $source);

        return \sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $tag,
            $htmlAttributes,
            $content,
        );
    }
}
