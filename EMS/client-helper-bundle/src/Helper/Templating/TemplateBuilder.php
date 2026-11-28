<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\ClientHelperBundle\Helper\Builder\AbstractBuilder;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Translation\TwigExtractor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment as TwigEnvironment;

final class TemplateBuilder extends AbstractBuilder
{
    /**
     * @param string[] $locales
     */
    public function __construct(
        ClientRequestManager $manager,
        LoggerInterface $logger,
        array $locales,
        int $searchLimit,
        private readonly TwigEnvironment $twig,
    ) {
        parent::__construct($manager, $logger, $locales, $searchLimit);
    }

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

    public function getCacheKey(Environment $environment, TemplateName $templateName): string
    {
        if ($environment->isLocalPulled()) {
            return \sprintf('twig_%s_%s_local', $environment->getAlias(), $templateName->getSearchName());
        }
        $settings = $this->clientRequest->getSettings($environment);
        $contentType = $settings->getTemplateContentType($templateName->getContentType());

        return \sprintf('twig_%s_%s_%d_%d', $environment->getAlias(), $templateName->getSearchName(), $contentType->getLastPublished()->getTimestamp(), $contentType->getTotal());
    }

    /**
     * @return array<string, true>
     */
    public function extractTranslationKeys(Environment $environment): array
    {
        $files = [];
        $finder = Finder::create()
            ->files()
            ->in($environment->getLocal()->getDirectory())
            ->name('*.twig');

        foreach ($finder as $file) {
            $files[] = $file->getPathname();
        }

        $catalogue = new MessageCatalogue('en');
        new TwigExtractor($this->twig)->extract($files, $catalogue);

        $keys = [];
        foreach ($catalogue->all() as $messages) {
            foreach ($messages as $key => $message) {
                $keys[(string) $key] = true;
            }
        }
        \ksort($keys, \SORT_STRING);

        return $keys;
    }
}
