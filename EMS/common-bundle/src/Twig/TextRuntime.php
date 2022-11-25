<?php

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Text\EmsHtml;
use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CommonBundle\Json\Decoder;
use EMS\CommonBundle\Json\JsonMenu;
use EMS\CommonBundle\Json\JsonMenuNested;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Extension\RuntimeExtensionInterface;

class TextRuntime implements RuntimeExtensionInterface
{
    private Encoder $encoder;
    private Decoder $decoder;
    private LoggerInterface $logger;

    public function __construct(Encoder $encoder, Decoder $decoder, LoggerInterface $logger)
    {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->logger = $logger;
    }

    public function emsHtml(string $html): EmsHtml
    {
        return new EmsHtml($html);
    }

    public function htmlEncode(string $text): string
    {
        return $this->encoder->htmlEncode($text);
    }

    public function htmlDecode(string $text, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, string $encoding = 'UTF-8'): string
    {
        return $this->encoder->htmlDecode($text, $flags, $encoding);
    }

    public function htmlEncodePii(string $text): string
    {
        return $this->encoder->htmlEncodePii($text);
    }

    public function jsonMenuDecode(string $json, string $glue = '/'): JsonMenu
    {
        return $this->decoder->jsonMenuDecode($json, $glue);
    }

    public function jsonMenuNestedDecode(string $json): JsonMenuNested
    {
        return $this->decoder->jsonMenuNestedDecode($json);
    }

    /**
     * @param int<1, 512> $depth
     *
     * @return mixed
     */
    public function jsonDecode(string $json, bool $assoc = true, int $depth = 512, int $options = 0)
    {
        return \json_decode($json, $assoc, $depth, $options);
    }

    /**
     * @return string|string[]|null
     */
    public function replaceRegex(string $text, string $pattern, string $replacement)
    {
        try {
            return \preg_replace($pattern, $replacement, $text);
        } catch (\Throwable $e) {
            $this->logger->warning('EMS replace regex failed', [
                'text' => $text,
                'pattern' => $pattern,
                'replacement' => $replacement,
                'exception' => $e,
            ]);

            return $text;
        }
    }

    public function domCrawler(string $node, string $uri = null, string $baseHref = null): Crawler
    {
        return new Crawler($node, $uri, $baseHref);
    }
}
