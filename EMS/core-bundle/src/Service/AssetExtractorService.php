<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CoreBundle\Entity\CacheAssetExtractor;
use EMS\CoreBundle\Helper\AssetExtractor\ExtractedData;
use EMS\CoreBundle\Tika\TikaJar;
use EMS\Helpers\File\File;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Number;
use EMS\Helpers\Standard\Type;
use Psr\Log\LoggerInterface;

class AssetExtractorService
{
    private const string CONTENT_EP = '/tika';
    private const string HELLO_EP = '/tika';
    private const string META_EP = '/meta';

    public function __construct(
        private readonly RestClientService $rest,
        private readonly LoggerInterface $logger,
        private readonly Registry $doctrine,
        private readonly FileService $fileService,
        private readonly TikaJar $tikaJar,
        private readonly ?string $tikaServer,
        private readonly int $tikaMaxContent = 5120,
    ) {
    }

    /**
     * @return array{code:int,content:string}
     */
    public function hello(): array
    {
        if (!empty($this->tikaServer)) {
            $client = $this->rest->getClient($this->tikaServer, 3);
            $result = $client->get(self::HELLO_EP);

            return [
                'code' => $result->getStatusCode(),
                'content' => $result->getBody()->__toString(),
                'client' => 'Tika',
            ];
        }
        $tempFile = TempFile::create();
        File::putContents($tempFile->path, "elasticms's built in TikaWrapper : àêïôú");

        return [
            'code' => 200,
            'content' => self::cleanString($this->tikaJar->getWrapper()->getText($tempFile->path)),
        ];
    }

    public function findCachedExtractedData(string $hash): ?ExtractedData
    {
        $manager = $this->doctrine->getManager();
        $repository = $manager->getRepository(CacheAssetExtractor::class);

        /** @var ?CacheAssetExtractor $cacheData */
        $cacheData = $repository->findOneBy(['hash' => $hash]);

        if ($cacheData instanceof CacheAssetExtractor) {
            return new ExtractedData($cacheData->getData() ?? [], $this->tikaMaxContent);
        }

        return null;
    }

    public function extractMetaData(string $hash, ?string $file = null, bool $forced = false, ?string $filename = null): ExtractedData
    {
        if (null !== $extractedData = $this->findCachedExtractedData($hash)) {
            return $extractedData;
        }

        $filesize = $this->fileService->getSize($hash);
        if (!$forced && $filesize > (3 * 1024 * 1024)) {
            $this->logger->warning('log.warning.asset_extract.file_to_large', [
                'filesize' => Number::formatBytes($filesize),
                'max_size' => '3 MB',
            ]);

            return new ExtractedData([], $this->tikaMaxContent);
        }

        if ((null === $file) || !\file_exists($file)) {
            $file = $this->fileService->getFile($hash);
        }
        if (!$file || !\file_exists($file)) {
            throw new NotFoundException($hash);
        }
        $canBePersisted = true;
        if (!empty($this->tikaServer)) {
            try {
                $client = $this->rest->getClient($this->tikaServer, $forced ? 900 : 30);
                $body = \file_get_contents($file);
                $result = $client->put(self::META_EP, [
                    'body' => $body,
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);
                $out = ExtractedData::fromJsonString($result->getBody()->__toString(), $this->tikaMaxContent);

                $result = $client->put(self::CONTENT_EP, [
                    'body' => $body,
                    'headers' => [
                        'Accept' => MimeTypeHelper::TEXT_PLAIN,
                    ],
                ]);
                $out->setContent($result->getBody()->__toString());
            } catch (\Exception $e) {
                $this->logger->warning('service.asset_extractor.extract_error', [
                    'file_hash' => $hash,
                    'filename' => $filename ?? $hash,
                    EmsFields::LOG_ERROR_MESSAGE_FIELD => $e->getMessage(),
                    EmsFields::LOG_EXCEPTION_FIELD => $e,
                    'tika' => 'server',
                ]);
                $canBePersisted = false;
            }
        } else {
            try {
                $out = ExtractedData::fromMetaString($this->tikaJar->getWrapper()->getMetadata($file), $this->tikaMaxContent);
                if (!$out->hasContent()) {
                    $text = $this->tikaJar->getWrapper()->getText($file);
                    if (!\mb_check_encoding($text)) {
                        $text = \mb_convert_encoding($text, \mb_internal_encoding(), 'ASCII');
                    }
                    $text = \preg_replace('/(\n)(\s*\n)+/', '${1}', Type::string($text));
                    $out->setContent($text ?? '');
                }
                if (!empty($out->getLocale())) {
                    $out->setLocale(self::cleanString($this->tikaJar->getWrapper()->getLanguage($file)));
                }
            } catch (\Exception $e) {
                $this->logger->warning('service.asset_extractor.extract_error', [
                    'file_hash' => $hash,
                    'filename' => $filename ?? $hash,
                    EmsFields::LOG_ERROR_MESSAGE_FIELD => $e->getMessage(),
                    EmsFields::LOG_EXCEPTION_FIELD => $e,
                    'tika' => 'jar',
                ]);
                $canBePersisted = false;
            }
        }

        if ($canBePersisted && isset($out)) {
            $cacheData = new CacheAssetExtractor();
            $cacheData->setHash($hash);
            $cacheData->setData($out->getSource());

            $manager = $this->doctrine->getManager();
            $manager->persist($cacheData);
            $manager->flush();
        }

        return $out ?? new ExtractedData([], $this->tikaMaxContent);
    }

    private static function cleanString(string $string): string
    {
        if (!\mb_check_encoding($string)) {
            $string = \mb_convert_encoding($string, \mb_internal_encoding(), 'ASCII');
        }
        if (!\is_string($string)) {
            throw new \RuntimeException('Unexpected issue while multi byte encoded data');
        }

        return \preg_replace('/\n|\r/', '', $string) ?? '';
    }

    public function getMetaFromText(string $text): ExtractedData
    {
        if (!empty($this->tikaServer)) {
            $client = $this->rest->getClient($this->tikaServer, 15);
            $result = $client->put(self::META_EP, [
                'body' => $text,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $meta = ExtractedData::fromJsonString($result->getBody()->__toString(), $this->tikaMaxContent);
        } else {
            $tempFile = TempFile::create();
            File::putContents($tempFile->path, $text);
            $meta = ExtractedData::fromMetaString($this->tikaJar->getWrapper()->getMetadata($tempFile->path), $this->tikaMaxContent);
        }

        return $meta;
    }
}
