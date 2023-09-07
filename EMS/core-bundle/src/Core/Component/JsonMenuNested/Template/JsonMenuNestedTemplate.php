<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested\Template;

use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfigException;
use EMS\Helpers\Standard\Json;
use Twig\Environment;
use Twig\TemplateWrapper;

class JsonMenuNestedTemplate
{
    private TemplateWrapper $template;
    private ?TemplateWrapper $configTemplate;

    public const TWIG_TEMPLATE = '@EMSCore/components/json_menu_nested/template.twig';

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly JsonMenuNestedConfig $config,
        private readonly Environment $twig,
        private array $context = []
    ) {
        $this->template = $this->twig->load(self::TWIG_TEMPLATE);
        $this->configTemplate = $this->config->template ? $this->twig->load($this->config->template) : null;

        $this->context = [...$this->context, ...$this->buildContextBlock($config->contextBlock)];
    }

    /**
     * @param array<mixed> $blockContext
     */
    public function block(string $blockName, array $blockContext = []): string
    {
        $context = $this->buildContext($blockContext);
        $isPrivate = \str_starts_with($blockName, '_');

        if (!$isPrivate && $this->configTemplate && $this->configTemplate->hasBlock($blockName)) {
            return $this->configTemplate->renderBlock($blockName, $context);
        }

        return $this->template->renderBlock($blockName, $context);
    }

    public function hasBlock(string $blockName): bool
    {
        return $this->configTemplate?->hasBlock($blockName) || $this->template->hasBlock($blockName);
    }

    /**
     * @param array<string, mixed> $blockContext
     *
     * @return array<string, mixed>
     */
    private function buildContext(array $blockContext): array
    {
        $context = [...$blockContext, ...$this->context, ...$this->config->context];
        $context['template'] = $this;
        $context['config'] = $this->config;

        return $context;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContextBlock(?string $contextBlock): array
    {
        if (null === $contextBlock || null === $this->configTemplate) {
            return [];
        }

        if (!$this->configTemplate->hasBlock($contextBlock)) {
            throw new JsonMenuNestedConfigException(\sprintf('Context block "%s" not defined', $contextBlock));
        }

        $blockResult = $this->block($contextBlock);
        if (!Json::isJson($blockResult)) {
            throw new JsonMenuNestedConfigException(\sprintf('Context block "%s" not returning json', $contextBlock));
        }

        return Json::decode($blockResult);
    }
}
