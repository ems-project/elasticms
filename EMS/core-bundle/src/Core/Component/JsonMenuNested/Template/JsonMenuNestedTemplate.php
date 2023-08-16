<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested\Template;

use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use Twig\Environment;
use Twig\TemplateWrapper;

class JsonMenuNestedTemplate
{
    private TemplateWrapper $template;
    private ?TemplateWrapper $configTemplate;

    private const TWIG_TEMPLATE = '@EMSCore/components/json_menu_nested/template.twig';

    public function __construct(
        private readonly JsonMenuNestedConfig $config,
        private readonly Environment $twig
    ) {
        $this->template = $this->twig->load(self::TWIG_TEMPLATE);
        $this->configTemplate = $this->config->template ? $this->twig->load($this->config->template) : null;
    }

    /**
     * @param array<mixed> $context
     */
    public function block(string $blockName, array $context = []): string
    {
        $context = $this->getContext($context);
        $isPrivate = \str_starts_with($blockName, '_');

        if (!$isPrivate && $this->configTemplate && $this->configTemplate->hasBlock($blockName)) {
            return $this->configTemplate->renderBlock($blockName, $context);
        }

        return $this->template->renderBlock($blockName, $context);
    }

    /**
     * @param array<mixed> $blockContext
     *
     * @return array<mixed>
     */
    private function getContext(array $blockContext): array
    {
        $context = [...$blockContext, ...$this->config->context];
        $context['template'] = $this;
        $context['config'] = $this->config;

        return $context;
    }
}
