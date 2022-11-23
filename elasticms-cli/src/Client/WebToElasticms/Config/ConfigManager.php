<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Config;

use App\Client\HttpClient\CacheManager;
use App\Client\WebToElasticms\Helper\Url;
use App\Client\WebToElasticms\Rapport\Rapport;
use App\ExpressionLanguage\Functions;
use EMS\CommonBundle\Common\Standard\Json;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Helper\EmsFields;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ConfigManager
{
    /** @var Document[] */
    private array $documents;

    /** @var Analyzer[] */
    private array $analyzers;

    /** @var Type[] */
    private array $types;

    /** @var string[] */
    private array $hosts = [];

    /** @var string[] */
    private array $validClasses = [];
    /** @var string[] */
    private array $locales = [];
    /** @var string[] */
    private array $linkToClean = [];
    private CacheManager $cacheManager;
    private CoreApiInterface $coreApi;
    private LoggerInterface $logger;
    private ?ExpressionLanguage $expressionLanguage = null;
    private string $hashResourcesField = 'import_hash_resources';
    private ?string $autoDiscoverResourcesLink = null;
    private ?string $ignoreResourceLinkPattern = null;
    /** @var string[] */
    private array $linksByUrl = [];
    /** @var array<string, string[]> */
    private array $datalinksByUrl = [];
    /** @var string[] */
    private array $cleanTags = ['h1'];
    /**
     * @var array<string, string[]>
     */
    private array $documentsToClean = [];
    private ?string $lastUpdated = null;

    public function serialize(string $format = JsonEncoder::FORMAT): string
    {
        return self::getSerializer()->serialize($this, $format);
    }

    public static function deserialize(string $data, CacheManager $cache, CoreApiInterface $coreApi, LoggerInterface $logger, string $format = JsonEncoder::FORMAT): ConfigManager
    {
        $config = self::getSerializer()->deserialize($data, ConfigManager::class, $format);
        if (!$config instanceof ConfigManager) {
            throw new \RuntimeException('Unexpected non ConfigManager object');
        }
        $config->cacheManager = $cache;
        $config->coreApi = $coreApi;
        $config->logger = $logger;

        return $config;
    }

    private static function getSerializer(): Serializer
    {
        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();
        $propertyTypeExtractor = new PropertyInfoExtractor([$reflectionExtractor], [$phpDocExtractor, $reflectionExtractor], [$phpDocExtractor], [$reflectionExtractor], [$reflectionExtractor]);

        return new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(null, null, null, $propertyTypeExtractor),
        ], [
            new XmlEncoder(),
            new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES]), null),
        ]);
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @param Document[] $documents
     */
    public function setDocuments(array $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @return Analyzer[]
     */
    public function getAnalyzers(): array
    {
        return $this->analyzers;
    }

    /**
     * @param Analyzer[] $analyzers
     */
    public function setAnalyzers(array $analyzers): void
    {
        $this->analyzers = $analyzers;
    }

    public function getAnalyzer(string $analyzerName): Analyzer
    {
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->getName() === $analyzerName) {
                return $analyzer;
            }
        }

        throw new \RuntimeException(\sprintf('Analyzer %s not found', $analyzerName));
    }

    /**
     * @return string[]
     */
    public function getHosts(): array
    {
        if (empty($this->hosts)) {
            foreach ($this->documents as $document) {
                foreach ($document->getResources() as $resource) {
                    $url = new Url($resource->getUrl());
                    if (!\in_array($url->getHost(), $this->hosts)) {
                        $this->hosts[] = $url->getHost();
                    }
                }
            }
        }

        return $this->hosts;
    }

    /**
     * @param string[] $hosts
     */
    public function setHosts(array $hosts): void
    {
        $this->hosts = $hosts;
    }

    public function findInternalLink(Url $url, Rapport $rapport): string
    {
        if (isset($this->linksByUrl[$url->getPath()])) {
            return $this->linksByUrl[$url->getPath()];
        }

        if ($rapport->inUrlsNotFounds($url)) {
            return $url->getPath();
        }

        $path = $this->findInDocuments($url);
        if (null === $path) {
            $path = $this->downloadAsset($url, $rapport);
        }
        if (null === $path) {
            $path = $url->getPath();
            $rapport->addUrlNotFound($url);
            $this->logger->notice(\sprintf('Internal url not found: %s', $url->getUrl()));
        }

        if (null !== $url->getFragment()) {
            $path .= '#'.$url->getFragment();
        }
        if (null !== $url->getQuery()) {
            $path .= '?'.$url->getQuery();
        }

        return $path;
    }

    public function findDataLink(string $path, Rapport $rapport, string $currentUrl, string $type = ''): string
    {
        if (!empty($type) && isset($this->datalinksByUrl[$type][$path])) {
            return $this->datalinksByUrl[$type][$path];
        }

        $url = new Url($path, $currentUrl);
        if (null !== $this->findObjectIdInDocuments($url)) {
            return $this->findObjectIdInDocuments($url);
        }

        $rapport->inDataLinkNotFounds($path, $currentUrl);

        return '';
    }

    /**
     * @param string[] $paths
     *
     * @return string[]
     */
    public function findDataLinksArray(array $paths, string $type = ''): array
    {
        $results = [];
        foreach ($paths as $path) {
            if (!empty($type) && isset($this->datalinksByUrl[$type][$path])) {
                $results[] = $this->datalinksByUrl[$type][$path];
            }
        }

        return $results;
    }

    public function findDataLinkString(string $path, string $type = ''): ?string
    {
        if (!empty($type) && isset($this->datalinksByUrl[$type][$path])) {
            return $this->datalinksByUrl[$type][$path];
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getValidClasses(): array
    {
        return $this->validClasses;
    }

    /**
     * @param string[] $validClasses
     */
    public function setValidClasses(array $validClasses): void
    {
        $this->validClasses = $validClasses;
    }

    public function findInDocuments(Url $url): ?string
    {
        foreach ($this->documents as $document) {
            $ouuid = $document->getOuuid();
            foreach ($document->getResources() as $resource) {
                $resourceUrl = new Url($resource->getUrl());
                if ($resourceUrl->getPath() === $url->getPath()) {
                    return \sprintf('ems://object:%s:%s', $document->getType(), $ouuid);
                }
            }
        }

        return null;
    }

    private function findObjectIdInDocuments(Url $url): ?string
    {
        foreach ($this->documents as $document) {
            $ouuid = $document->getOuuid();
            foreach ($document->getResources() as $resource) {
                $resourceUrl = new Url($resource->getUrl());
                if ($resourceUrl->getPath() === $url->getPath()) {
                    return \sprintf('%s:%s', $document->getType(), $ouuid);
                }
            }
        }

        return null;
    }

    /**
     * @return array{filename: string, filesize: int|null, mimetype: string, sha1: string}|array{}
     */
    public function urlToAssetArray(Url $url, Rapport $rapport): array
    {
        $asset = $this->cacheManager->get($url->getUrl());
        $mimeType = $asset->getMimetype();
        if (200 != $asset->getResponse()->getStatusCode() || $asset->isHtml()) {
            return [];
        }
        $filename = $url->getFilename();
        $stream = $asset->getStream();
        $stream->seek(0);
        try {
            $hash = $this->coreApi->file()->uploadStream($stream, $filename, $mimeType);
        } catch (CoreApiExceptionInterface $e) {
            $rapport->inAssetsError($url->getUrl(), $url->getReferer());

            return [];
        }
        if (0 === \strlen($hash)) {
            throw new \RuntimeException('Unexpected empty hash');
        }

        return [
            EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
            EmsFields::CONTENT_FILE_NAME_FIELD => $filename,
            EmsFields::CONTENT_MIME_TYPE_FIELD => $mimeType,
            EmsFields::CONTENT_FILE_SIZE_FIELD => $asset->getStream()->getSize(),
        ];
    }

    private function downloadAsset(Url $url, Rapport $rapport): ?string
    {
        $assetArray = $this->urlToAssetArray($url, $rapport);

        if (empty($assetArray)) {
            return null;
        }

        return \sprintf('ems://asset:%s?name=%s&type=%s', $assetArray[EmsFields::CONTENT_FILE_HASH_FIELD], \urlencode($assetArray[EmsFields::CONTENT_FILE_NAME_FIELD]), \urlencode($assetArray[EmsFields::CONTENT_MIME_TYPE_FIELD]));
    }

    /**
     * @return string[]
     */
    public function getLinkToClean(): array
    {
        return $this->linkToClean;
    }

    /**
     * @param string[] $linkToClean
     */
    public function setLinkToClean(array $linkToClean): void
    {
        $this->linkToClean = $linkToClean;
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param Type[] $types
     */
    public function setTypes(array $types): void
    {
        $this->types = $types;
    }

    public function getType(string $name): Type
    {
        foreach ($this->types as $type) {
            if ($type->getName() === $name) {
                return $type;
            }
        }

        throw new \RuntimeException(\sprintf('Type %s not found', $name));
    }

    public function save(string $jsonPath, bool $finish = false): bool
    {
        if ($finish) {
            $this->lastUpdated = null;
        }

        return false !== \file_put_contents($jsonPath, $this->serialize());
    }

    public function getExpressionLanguage(): ExpressionLanguage
    {
        if (null !== $this->expressionLanguage) {
            return $this->expressionLanguage;
        }
        $this->expressionLanguage = new ExpressionLanguage();

        $this->expressionLanguage->register('uuid',
            fn () => '(\\Ramsey\\Uuid\\Uuid::uuid4()->toString())',
            fn ($arguments) => Uuid::uuid4()->toString()
        );

        $this->expressionLanguage->register('json_escape',
            fn ($str) => \sprintf('(null === %1$s ? null : \\EMS\\CommonBundle\\Common\\Standard\\Json::escape(%1$s))', $str),
            fn ($arguments, $str) => null === $str ? null : Json::escape($str)
        );

        $this->expressionLanguage->register('strtotime',
            fn ($str) => \sprintf('(null === %1$s ? null : \\strtotime(%1$s))', $str),
            fn ($arguments, $str) => null === $str ? null : \strtotime($str)
        );

        $this->expressionLanguage->register('date',
            fn ($format, $timestamp) => \sprintf('((null === %1$s || null === %2$s) ? null : \\date(%1$s, %2$s))', $format, $timestamp),
            fn ($arguments, $format, $timestamp) => (null === $format || null === $timestamp) ? null : \date($format, $timestamp)
        );

        $this->expressionLanguage->register('dom_to_json_menu',
            fn ($html, $tag, $fieldName, $typeName, $labelField) => \sprintf('((null === %1$s || null === %2$s || null === %3$s || null === %4$s || null === %5$s) ? null : \\App\\ExpressionLanguage\\Functions::domToJsonMenu(%1$s, %2$s, %3$s, %4$s, %5$s))', $html, $tag, $fieldName, $typeName, $labelField),
            fn ($arguments, $html, $tag, $fieldName, $typeName, $labelField) => (null === $html || null === $tag || null === $fieldName || null === $typeName || null === $labelField) ? null : Functions::domToJsonMenu($html, $tag, $fieldName, $typeName, $labelField)
        );

        $this->expressionLanguage->register('pa11y',
            fn ($url) => \sprintf('((null === %1$s) ? null : \\App\\ExpressionLanguage\\Functions::pa11y(%1$s))', $url),
            fn ($arguments, $url) => (null === $url) ? null : Functions::pa11y($url)
        );

        $this->expressionLanguage->register('split',
            fn ($pattern, $str, $limit = -1, $flags = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) => \sprintf('((null === %1$s || null === %2$s) ? null : \\preg_split(%1$s, %2$s, %3$d, %4$d))', $pattern, $str, $limit, $flags),
            fn ($arguments, $pattern, $str, $limit = -1, $flags = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) => (null === $pattern || null === $str) ? null : \preg_split($pattern, $str, $limit, $flags)
        );

        $this->expressionLanguage->register('datalinks',
            fn ($value, $type) => \sprintf('((null === %1$s || null === %2$s) ? null : (is_array($value) ? \\$this->findDataLinksArray(%1$s, %2$s): $this->findDataLinkString(%1$s, %2$s)))', \strval($value), $type),
            fn ($arguments, $value, $type) => (null === $value || null === $type) ? null : (\is_array($value) ? $this->findDataLinksArray($value, $type) : $this->findDataLinkString($value, $type))
        );

        $this->expressionLanguage->register('list_to_json_menu_nested',
            fn ($values, $fieldName, $typeName, $labels = null, $labelField = null, $multiplex = false) => \sprintf('((null === %1$s || null === %2$s || null === %3$s) ? null : \\App\\ExpressionLanguage\\Functions::listToJsonMenuNested(%1$s, %2$s, %3$s, %4$s, %5$s, %6$s))', \strval($values), $fieldName, $typeName, \strval($labels), $labelField, \strval($multiplex)),
            fn ($arguments, $values, $fieldName, $typeName, $labels = null, $labelField = null, $multiplex = false) => (null === $values || null === $fieldName || null === $typeName) ? null : Functions::listToJsonMenuNested($values, $fieldName, $typeName, $labels, $labelField, $multiplex)
        );

        return $this->expressionLanguage;
    }

    public function getHashResourcesField(): string
    {
        return $this->hashResourcesField;
    }

    public function setHashResourcesField(string $hashResourcesField): void
    {
        $this->hashResourcesField = $hashResourcesField;
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @param string[] $locales
     */
    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    public function getAutoDiscoverResourcesLink(): ?string
    {
        return $this->autoDiscoverResourcesLink;
    }

    public function setAutoDiscoverResourcesLink(?string $autoDiscoverResourcesLink): void
    {
        $this->autoDiscoverResourcesLink = $autoDiscoverResourcesLink;
    }

    public function getIgnoreResourceLinkPattern(): ?string
    {
        return $this->ignoreResourceLinkPattern;
    }

    public function setIgnoreResourceLinkPattern(?string $ignoreResourceLinkPattern): void
    {
        $this->ignoreResourceLinkPattern = $ignoreResourceLinkPattern;
    }

    /**
     * @return string[]
     */
    public function getLinksByUrl(): array
    {
        return $this->linksByUrl;
    }

    /**
     * @param string[] $linksByUrl
     */
    public function setLinksByUrl(array $linksByUrl): void
    {
        $this->linksByUrl = $linksByUrl;
    }

    /**
     * @return array<string, string[]>
     */
    public function getDataLinksByUrl(): array
    {
        return $this->datalinksByUrl;
    }

    /**
     * @param array<string, string[]> $datalinksByUrl
     */
    public function setDataLinksByUrl(array $datalinksByUrl): void
    {
        $this->datalinksByUrl = $datalinksByUrl;
    }

    /**
     * @return string[]
     */
    public function getCleanTags(): array
    {
        return $this->cleanTags;
    }

    /**
     * @param string[] $cleanTags
     */
    public function setCleanTags(array $cleanTags): void
    {
        $this->cleanTags = $cleanTags;
    }

    /**
     * @return array<string, string[]>
     */
    public function getDocumentsToClean(): array
    {
        return $this->documentsToClean;
    }

    /**
     * @param array<string, string[]> $documentsToClean
     */
    public function setDocumentsToClean(array $documentsToClean): void
    {
        $this->documentsToClean = $documentsToClean;
    }

    public function getLastUpdated(): ?string
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?string $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }
}
