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
        $formatter->parseString($source, \array_merge([
            'indent' => true,
            'indent-spaces' => 2,
            'newline' => 'LF',
            'wrap' => 68,
            'hide-comments' => 1,
            'drop-empty-elements' => false,
        ], $parseStringConfig));

        return \trim(\str_replace(["<body>\n  ", "\n</body>", "\n  ", '<body>'], ['', '', "\n", ''], $formatter->body()->value));
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
