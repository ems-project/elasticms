<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use cebe\markdown\GithubMarkdown;
use EMS\CommonBundle\Common\Text\EmsHtml;
use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CommonBundle\Json\Decoder;
use EMS\CommonBundle\Json\JsonMenu;
use EMS\CommonBundle\Json\JsonMenuNested;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

class TextExtension
{
    public function __construct(
        private readonly Encoder $encoder,
        private readonly Decoder $decoder,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsTwigFilter(name: 'ems_ascii_folding')]
    public function asciiFolding(string $text, ?string $locale = null): string
    {
        $rules = [];
        if ($locale && ('de' === $locale || \str_starts_with($locale, 'de_'))) {
            $rules = ['de-ASCII'];
        }

        return new UnicodeString($text)->ascii($rules)->toString();
    }

    /**
     * @param \DOMNode|\DOMNodeList<\DOMNode>|string|\DOMNode[]|null $node
     */
    #[AsTwigFilter(name: 'ems_dom_crawler')]
    public function domCrawler(\DOMNode|\DOMNodeList|array|string|null $node, ?string $uri = null, ?string $baseHref = null): Crawler
    {
        return new Crawler($node, $uri, $baseHref);
    }

    #[AsTwigFunction(name: 'ems_html', isSafe: ['all'])]
    public function emsHtml(string $html): EmsHtml
    {
        return new EmsHtml($html);
    }

    #[AsTwigFilter(name: 'ems_html_decode')]
    public function htmlDecode(string $text, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, string $encoding = 'UTF-8'): string
    {
        return $this->encoder->htmlDecode($text, $flags, $encoding);
    }

    #[AsTwigFilter(name: 'ems_html_encode', isSafe: ['html'])]
    public function htmlEncode(string $text): string
    {
        return $this->encoder->htmlEncode($text);
    }

    #[AsTwigFilter(name: 'ems_anti_spam', isSafe: ['html'])]
    public function htmlEncodePii(string $text): string
    {
        return $this->encoder->htmlEncodePii($text);
    }

    #[AsTwigFilter(name: 'ems_valid_mail')]
    public function isValidEmail(string $email): bool
    {
        $constraint = new Email();
        $violations = $this->validator->validate($email, $constraint);

        return 0 === \count($violations);
    }

    /**
     * @param int<1, 512> $depth
     */
    #[AsTwigFilter(name: 'ems_json_decode')]
    public function jsonDecode(string $json, bool $assoc = true, int $depth = 512, int $options = 0): mixed
    {
        return \json_decode($json, $assoc, $depth, $options);
    }

    #[AsTwigFilter(name: 'ems_json_menu_decode')]
    public function jsonMenuDecode(string $json, string $glue = '/'): JsonMenu
    {
        return $this->decoder->jsonMenuDecode($json, $glue);
    }

    #[AsTwigFilter(name: 'ems_json_menu_nested_decode')]
    public function jsonMenuNestedDecode(string $json): JsonMenuNested
    {
        return $this->decoder->jsonMenuNestedDecode($json);
    }

    #[AsTwigFilter(name: 'ems_markdown', isSafe: ['html'])]
    public function markdownToHtml(string $markdown): string
    {
        static $parser;
        if (null === $parser) {
            $parser = new GithubMarkdown();
        }

        return $parser->parse($markdown);
    }

    /**
     * @return array<mixed>
     */
    #[AsTwigFilter(name: 'ems_preg_match')]
    public function pregMatch(string $subject, string $pattern, int $flags = PREG_SET_ORDER, int $offset = 0): array
    {
        $matches = [];
        if (false === \preg_match_all($pattern, $subject, $matches, $flags, $offset)) {
            return [];
        }

        return $matches;
    }

    /**
     * @return string|string[]|null
     */
    #[AsTwigFilter(name: 'ems_replace_regex', isSafe: ['html'])]
    public function replaceRegex(string $text, string $pattern, string $replacement): array|string|null
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

    #[AsTwigFilter(name: 'ems_slug')]
    public function slug(string $text, ?string $locale = null, string $separator = '-', bool $lower = true, bool $preserveFileExtension = false): AbstractUnicodeString
    {
        return $this->encoder->slug($text, $locale, $separator, $lower, $preserveFileExtension);
    }
}
