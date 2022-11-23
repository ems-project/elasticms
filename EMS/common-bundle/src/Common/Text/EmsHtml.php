<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Text;

use Twig\Markup;

final class EmsHtml extends Markup
{
    private string $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }

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
        $pattern = '/<a(.|\s)*?href=\s*?"(?<href>(.|\s)*?)"(.|\s)*?>(?<content>(.|\s)*?)<\/a>/';

        $replaced = \preg_replace_callback($pattern, fn ($match) => \str_replace([':content', ':href'], [$match['content'], $match['href']], $format), $this->html);

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
}
