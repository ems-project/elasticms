<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Text;

use Twig\Markup;

final class EmsHtml extends Markup implements \Stringable
{
    public function __construct(private string $html)
    {
        parent::__construct($html, 'UTF-8');
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->html;
    }

    public function removeTag(string $tag, string $regexAttributes = '.*?', bool $keepContent = true): self
    {
        $pattern = \sprintf('/<%s%s>(?<content>.*?)<\/%s>/', $tag, $regexAttributes, $tag);

        $replaced = \preg_replace_callback($pattern, fn ($match) => $keepContent ? $match['content'] : '', $this->html);

        $this->html = \is_string($replaced) ? $replaced : $this->html;

        return $this;
    }

    public function printUrls(string $format = ':content (:href)'): self
    {
        $replaced = \preg_replace_callback(
            '/<a(.|\s)*?>(?<content>(.|\s)*?)<\/a>/',
            fn ($match) => $this->printUrl($match, $format),
            $this->html
        );

        $this->html = \is_string($replaced) ? $replaced : $this->html;

        return $this;
    }

    /**
     * @param array<array<string, string>> $replacements
     */
    public function replace(array $replacements): self
    {
        $search = \array_keys($replacements);
        $values = \array_values($replacements);

        $this->html = \str_replace($search, $values, $this->html);

        return $this;
    }

    /**
     * @param array<int|string, string> $match
     */
    private function printUrl(array $match, string $format): string
    {
        $link = $match[0];
        \preg_match('/<a\s+(?:[^>]*?\s+)?href=(["\'])(?<href>.*?)\1/', $link, $matchHref);

        $content = $match['content'];
        $href = $matchHref['href'] ?? null;

        if (null === $href || \str_starts_with($href, '#')) {
            return $link;
        }

        return \str_replace([':content', ':href'], [$content, $href], $format);
    }
}
