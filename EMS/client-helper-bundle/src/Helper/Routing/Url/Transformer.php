<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing\Url;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Twig\AssetRuntime;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class Transformer
{
    private readonly ClientRequest $clientRequest;
    private readonly string $template;
    /** @var array<string, mixed> */
    private array $documents = [];

    public function __construct(private readonly AssetRuntime $assetRuntime, ClientRequestManager $clientRequestManager, private readonly Generator $generator, private readonly Environment $twig, private readonly LoggerInterface $logger, ?string $template)
    {
        $this->clientRequest = $clientRequestManager->getDefault();
        $this->template = $template ?? '@EMSCH/template/{type}.ems_link.twig';
    }

    public function getGenerator(): Generator
    {
        return $this->generator;
    }

    /**
     * @param array<mixed> $match
     * @param array<mixed> $config
     */
    private function generate(array $match, array $config = []): ?string
    {
        try {
            $emsLink = EMSLink::fromMatch($match);

            if (\str_ends_with('asset', $emsLink->getLinkType())) {
                return $this->generateForAsset($emsLink, $match, $config);
            }

            if (!$emsLink->hasContentType()) {
                throw new \Exception('missing content type');
            }

            $context = $this->makeContext($emsLink, $config);
            $template = \str_replace('{type}', $emsLink->getContentType(), $this->template);
            $url = $this->twigRender($template, $context);

            if ($url) {
                return $this->generator->prependBaseUrl($url);
            }

            return null;
        } catch (\Exception $ex) {
            $this->logger->error(\sprintf('%s match (%s)', $ex->getMessage(), Json::encode($match)));

            return null;
        }
    }

    /**
     * @param array<mixed> $config
     */
    public function transform(string $content, array $config = []): string
    {
        $transform = \preg_replace_callback(EMSLink::PATTERN, function ($match) use ($config) {
            $cleanMatch = \array_filter($match);

            if (0 === \count($cleanMatch)) {
                return $match[0];
            }

            $generation = $this->generate($cleanMatch, $config);
            $route = ($generation ?? $match[0]);
            $srcAttribute = '' !== $match['src'];
            if ('asset' === $match['link_type'] && ($config['asset_file_path'] ?? false) && $srcAttribute) {
                $baseUrl = '';
            } else {
                $baseUrl = $config['baseUrl'] ?? '';
            }
            $transformed = $baseUrl.$route;
            if (\strlen($match['query'] ?? '') > 0) {
                $transformed = \join('?', [$transformed, $match['query']]);
            }

            return $srcAttribute ? 'src="'.$transformed : $transformed;
        }, $content);

        return \is_string($transform) ? $transform : $content;
    }

    /**
     * @param array<mixed> $match
     * @param array<mixed> $config
     */
    private function generateForAsset(EMSLink $emsLink, array $match, array $config = []): string
    {
        $assetConfig = [];
        $assetFilePaths = $config['asset_file_path'] ?? false;

        if ($assetFilePaths && isset($match['src'])) {
            $assetConfig = [EmsFields::ASSET_CONFIG_GET_FILE_PATH => true];
        } elseif ($assetFilePaths) {
            $assetConfig = [EmsFields::ASSET_CONFIG_URL_TYPE => UrlGeneratorInterface::NETWORK_PATH];
        }

        return $this->assetRuntime->assetPath([
            EmsFields::CONTENT_FILE_HASH_FIELD => $emsLink->getOuuid(),
            EmsFields::CONTENT_FILE_NAME_FIELD => $emsLink->getQuery()['name'] ?? 'asset',
            EmsFields::CONTENT_MIME_TYPE_FIELD => $emsLink->getQuery()['type'] ?? MimeTypes::APPLICATION_OCTET_STREAM->value,
        ], $assetConfig);
    }

    /**
     * @param array<mixed> $context
     */
    private function twigRender(string $template, array $context): ?string
    {
        try {
            return $this->twig->render($template, $context);
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage());
        }

        return null;
    }

    /**
     * @param array<mixed> $config
     *
     * @return array<mixed>
     */
    private function makeContext(EMSLink $emsLink, array $config): array
    {
        $context = $config['context'] ?? [];
        $context['url'] = $emsLink;

        $dynamicTypes = $config['dynamic_types'] ?? [];
        if (!\in_array($emsLink->getContentType(), $dynamicTypes)) {
            if ($document = $this->getDocument($emsLink)) {
                $context['id'] = $document['_id'];
                $context['source'] = $document['_source'];
            }
        }

        if (isset($config['locale'])) {
            $context['locale'] = $config['locale'];
        }
        if (!isset($context['locale'])) {
            $context['locale'] = $this->clientRequest->getLocale();
        }

        return $context;
    }

    /**
     * @return array<mixed>
     */
    private function getDocument(EMSLink $emsLink): ?array
    {
        if (isset($this->documents[$emsLink->__toString()])) {
            return $this->documents[$emsLink->__toString()];
        }

        $document = $this->clientRequest->getByOuuid(
            $emsLink->getContentType(),
            $emsLink->getOuuid(),
            [],
            ['*.content', '*.attachement', '*._attachement']
        );

        if (!$document) {
            throw new \Exception('Document not found for : '.$emsLink);
        }
        $this->documents[$emsLink->__toString()] = $document;

        return $document;
    }
}
