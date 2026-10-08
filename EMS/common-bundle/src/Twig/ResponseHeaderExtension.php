<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderManager;
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
}
