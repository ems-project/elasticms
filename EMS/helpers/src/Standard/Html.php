<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

use EMS\Helpers\Html\Sanitizer\HtmlSanitizerConfigBuilder;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

class Html implements \Stringable
{
    private string $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    /**
     * @param array<mixed> $settings
     */
    public function sanitize(array $settings = []): Html
    {
        $configBuilder = new HtmlSanitizerConfigBuilder($settings);

        return new Html((new HtmlSanitizer($configBuilder->build()))->sanitize($this->html));
    }

    public function __toString(): string
    {
        return $this->html;
    }

    /**
     * @param array<mixed> $settings
     */
    public function prettyPrint(array $settings = []): Html
    {
        $formatter = new \tidy();
        $formatter->parseString($this->html, \array_merge([
            'indent' => true,
            'indent-spaces' => 2,
            'newline' => 'LF',
            'wrap' => 68,
            'hide-comments' => 1,
            'drop-empty-elements' => false,
        ], $settings));

        return new Html(\trim(\str_replace(
            ["<body>\n  ", "\n</body>", "\n  ", '<body>'],
            ['', '', "\n", ''],
            $formatter->body()->value)
        ));
    }
}
