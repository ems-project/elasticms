<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Builder;

use EMS\ClientHelperBundle\Helper\ContentType\ContentType;
use EMS\ClientHelperBundle\Helper\Elasticsearch\Settings;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Routing\RoutingBuilder;
use EMS\ClientHelperBundle\Helper\Templating\TemplateBuilder;
use EMS\ClientHelperBundle\Helper\Translation\TranslationBuilder;
use EMS\CommonBundle\Common\Standard\Hash;

final class Builders
{
    public function __construct(private readonly RoutingBuilder $routing, private readonly TemplateBuilder $templating, private readonly TranslationBuilder $translation)
    {
    }

    /**
     * Returns the combined cache validity tags of all used contentTypes.
     */
    public function getVersion(Settings $settings): string
    {
        $contentTypes = $settings->getContentTypes();

        $cacheValidityTags = \array_reduce(
            $contentTypes,
            fn (string $key, ContentType $contentType) => $key.$contentType->getCacheValidityTag(),
            ''
        );

        return Hash::string($cacheValidityTags);
    }

    public function build(Environment $environment, string $directory): void
    {
        $this->translation->buildFiles($environment, $directory);
        $templateFiles = $this->templating->buildFiles($environment, $directory);
        $this->routing->buildFiles($environment, $templateFiles, $directory);
    }

    public function routing(): RoutingBuilder
    {
        return $this->routing;
    }

    public function templating(): TemplateBuilder
    {
        return $this->templating;
    }

    public function translation(): TranslationBuilder
    {
        return $this->translation;
    }
}
