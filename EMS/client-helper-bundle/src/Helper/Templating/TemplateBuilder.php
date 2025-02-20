<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\ClientHelperBundle\Helper\Builder\AbstractBuilder;
use EMS\ClientHelperBundle\Helper\Environment\Environment;

final class TemplateBuilder extends AbstractBuilder
{
    public function buildTemplate(Environment $environment, TemplateName $templateName): TemplateDocument
    {
        $settings = $this->settings($environment);

        $contentType = $settings->getTemplateContentType($templateName->getContentType());
        $mapping = $settings->getTemplateMapping($templateName->getContentType());

        $searchField = $templateName->getSearchField();
        $hit = $this->clientRequest->searchOne($contentType->getName(), [
            '_source' => [$mapping['name'], $mapping['code']],
            'query' => [
                'term' => [
                    ($searchField ?? $mapping['name']) => $templateName->getSearchValue(),
                ],
            ],
        ], $contentType->getEnvironment()->getAlias());

        return new TemplateDocument($hit['_id'], $hit['_source'], $mapping);
    }

    public function buildFile(Environment $environment, TemplateName $templateName): TemplateFile
    {
        $settings = $this->settings($environment);

        return $environment->getLocal()->getTemplates($settings)->get($templateName);
    }

    public function buildFiles(Environment $environment, string $directory): TemplateFiles
    {
        $settings = $this->settings($environment);

        return TemplateFiles::build($directory, $settings, $this->getDocuments($environment));
    }

    /**
     * @return \Generator|TemplateDocument[]
     */
    public function getDocuments(Environment $environment): \Generator
    {
        $settings = $this->settings($environment);

        foreach ($settings->getTemplateContentTypes() as $contentType) {
            $mapping = $settings->getTemplateMapping($contentType->getName());

            foreach ($this->search($contentType)->getDocuments() as $doc) {
                yield new TemplateDocument($doc->getId(), $doc->getSource(), $mapping);
            }
        }
    }

    public function isFresh(Environment $environment, TemplateName $templateName, int $time): bool
    {
        $settings = $this->clientRequest->getSettings($environment);

        if ($environment->isLocalPulled()) {
            return $environment->getLocal()->getTemplates($settings)->get($templateName)->isFresh($time);
        }

        $contentType = $settings->getTemplateContentType($templateName->getContentType());

        return $contentType->isLastPublishedAfterTime($time);
    }
}
