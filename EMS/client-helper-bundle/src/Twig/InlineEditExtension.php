<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use Twig\Attribute\AsTwigFunction;

class InlineEditExtension
{
    /**
     * @param array<string, string> $attributes
     */
    #[AsTwigFunction(name: 'emsch_inline_edit', isSafe: ['html'])]
    public function renderElement(string $element, DocumentInterface $document, string $path, array $attributes = []): string
    {
        $content = $document->getValue($path);

        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= \sprintf(' %s="%s"', $key, \htmlspecialchars($value, ENT_QUOTES));
        }

        return \sprintf('<%1$s%2$s>%3$s</%1$s>', $element, $attrString, \htmlspecialchars($content));
    }
}
