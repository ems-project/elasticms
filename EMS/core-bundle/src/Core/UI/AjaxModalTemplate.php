<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\UI;

use Twig\TemplateWrapper;

class AjaxModalTemplate
{
    public function __construct(
        private readonly TemplateWrapper $template,
        private readonly string $blockPrefix,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{
     *      modalTitle?: string,
     *      modalBody?: string,
     *      modalFooter?: string,
     * }
     */
    public function render(array $context): array
    {
        return \array_filter([
            'modalTitle' => $this->renderBlock('title', $context),
            'modalBody' => $this->renderBlock('body', $context),
            'modalFooter' => $this->renderBlock('footer', $context),
        ], static fn ($value) => null !== $value);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderBlock(string $block, array $context): ?string
    {
        $blockName = $this->blockPrefix.$block;

        if (!$this->template->hasBlock($blockName)) {
            return null;
        }

        return $this->template->renderBlock($blockName, $context);
    }
}
