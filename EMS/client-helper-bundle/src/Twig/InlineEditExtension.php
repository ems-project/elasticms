<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;

readonly class InlineEditExtension
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * @param array<string, string> $attributes
     */
    #[AsTwigFunction(name: 'emsch_inline_edit', isSafe: ['html'])]
    public function renderElement(string $element, DocumentInterface $document, string $path, array $attributes = []): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $emschRequest = $request ? EmschRequest::fromRequest($request) : null;
        $inlineEdit = $emschRequest && $emschRequest->isInlineEditorEnabled();

        if ($inlineEdit) {
            if (isset($attributes['class'])) {
                $attributes['class'] .= ' inline-edit-element';
            } else {
                $attributes['class'] = 'inline-edit-element';
            }

            $attributes['data-ems-id'] = (string) $document->getEmsLink();
            $attributes['data-path'] = $path;
        }

        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= \sprintf(' %s="%s"', $key, \htmlspecialchars($value, ENT_QUOTES));
        }

        $content = $document->getValue($path);

        return \sprintf('<%1$s%2$s>%3$s</%1$s>', $element, $attrString, \htmlspecialchars((string) $content));
    }
}
