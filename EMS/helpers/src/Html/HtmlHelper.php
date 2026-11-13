<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

use EMS\Helpers\Standard\Type;

class HtmlHelper
{
    /**
     * @param mixed[] $parseStringConfig
     */
    public static function prettyPrint(?string $source, array $parseStringConfig = []): string
    {
        $source ??= '';
        $formatter = new \tidy();
        $enclosed = self::encloseInlineContent($source);
        $formatter->parseString($enclosed, \array_merge([
            'indent' => true,
            'indent-spaces' => 2,
            'newline' => 'LF',
            'wrap' => 68,
            'hide-comments' => 1,
            'drop-empty-elements' => false,
        ], $parseStringConfig));

        $pretty = \trim(\str_replace(["<body>\n  ", "\n</body>", "\n  ", '<body>'], ['', '', "\n", ''], $formatter->body()->value));

        if ($enclosed !== $source) {
            return \preg_replace(['/^<p>\s+/', '/\s+<\/p>$/'], ['<p>', '</p>'], $pretty) ?? $pretty;
        }

        return $pretty;
    }

    /**
     * Newer libtidy versions no longer wrap inline-only content in a paragraph, older ones did.
     */
    private static function encloseInlineContent(string $source): string
    {
        $withoutComments = \preg_replace('/<!--.*?-->/s', '', $source) ?? $source;
        if ('' === \trim($withoutComments)) {
            return $source;
        }
        $blockTags = 'address|article|aside|blockquote|body|dd|details|div|dl|dt|fieldset|figure|footer|form|h[1-6]|head|header|hr|html|li|main|nav|ol|p|pre|section|table|ul';
        if (1 === \preg_match('/<(?:'.$blockTags.')[\s>\/]/i', $withoutComments)) {
            return $source;
        }

        return '<p>'.$source.'</p>';
    }

    public static function compare(?string $html1, ?string $html2): int
    {
        $html1 ??= '';
        $html2 ??= '';
        $formatterHtml1 = new \tidy();
        $formatterHtml1->parseString($html1);
        $formatterHtml2 = new \tidy();
        $formatterHtml2->parseString($html2);

        return \strcmp($formatterHtml1->html()->value, $formatterHtml1->html()->value);
    }

    public static function isHtml(?string $source): bool
    {
        $source ??= '';

        return $source !== \strip_tags($source);
    }

    public static function stripZeroWidthCharacters(?string $sourceHtml): ?string
    {
        if (null === $sourceHtml) {
            return null;
        }

        return Type::string(\str_replace(['​', '­'], ['', ''], $sourceHtml));
    }
}
