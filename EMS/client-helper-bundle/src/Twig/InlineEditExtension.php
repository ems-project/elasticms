<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Helper\InlineEdit\InlineEditConfigFactory;
use EMS\Helpers\Standard\UuidGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;
use Twig\Extension\AbstractExtension;

class InlineEditExtension extends AbstractExtension
{
    public const REQUEST_INLINE_EDIT = '_emsch_inline_edit';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    #[AsTwigFunction(name: 'emsch_inline_edit', isSafe: ['html'])]
    public function renderElement(array $options): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $requestInline = $request?->attributes->getBoolean(self::REQUEST_INLINE_EDIT) ?? false;

        $config = InlineEditConfigFactory::fromArray($options);
        $attributes = $config->attributes;

        if ($requestInline) {
            $attributes['data-ems-id'] = (string) $config->document->getEmsLink();
            $attributes['data-path'] = $config->path;
            $attributes['data-inline-id'] = UuidGenerator::random();
        }

        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= \sprintf(' %s="%s"', $key, \htmlspecialchars((string) $value, ENT_QUOTES));
        }

        $content = $config->document->getValue($config->path);

        return \sprintf('<%1$s%2$s>%3$s</%1$s>', $config->element, $attrString, $content);
    }
}
