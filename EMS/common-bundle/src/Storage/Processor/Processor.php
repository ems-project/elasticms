<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Processor;

use EMS\CommonBundle\Helper\Cache;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\File\LocalFile;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\File;
use EMS\Helpers\Html\Headers;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Processor
{
    public function __construct(
        private readonly StorageManager $storageManager,
        private readonly LoggerInterface $logger,
        private readonly Cache $cacheHelper,
        private readonly FileLocator $fileLocator,
    ) {
    }

    /**
     * @param mixed[] $fileField
     * @param mixed[] $configArray
     */
    public function resolveAndGetResponse(Request $request, array $fileField, array $configArray = [], bool $immutableRoute = false): Response
    {
        $hash = Config::extractHash($fileField, EmsFields::CONTENT_FILE_HASH_FIELD, \strval($configArray[EmsFields::ASSET_CONFIG_TYPE] ?? 'none'));
        $filename = Config::extractFilename($fileField, $configArray);
        $mimetype = Config::extractMimetype($fileField, $configArray, $filename);
        $mimeType = $this->overwriteMimeType($mimetype, $configArray);
        $filename = Config::fixFileExtension($filename, $mimeType);
        $configArray[EmsFields::ASSET_CONFIG_MIME_TYPE] = $mimeType;
        $config = $this->configFactory($hash, $configArray);

        return $this->getStreamedResponse($request, $config, $filename, $immutableRoute);
    }

    public function getResponse(Request $request, string $hash, string $configHash, string $filename, bool $immutableRoute = false): Response
    {
        $configJson = Json::decode($this->storageManager->getContents($configHash));
        $config = new Config($this->storageManager, $hash, $configHash, $configJson);

        return $this->getStreamedResponse($request, $config, $filename, $immutableRoute);
    }

    public function getStreamedResponse(Request $request, Config $config, string $filename, bool $immutableRoute): Response
    {
        if (!$config->isAvailabe()) {
            throw new AccessDeniedHttpException();
        }

        $authorization = \strval($request->headers->get(Headers::AUTHORIZATION));
        if (!$config->isAuthorized($authorization)) {
            $response = new Response('Unauthorized access', Response::HTTP_UNAUTHORIZED);
            $response->headers->set(Headers::WWW_AUTHENTICATE, 'basic realm="Access to resource"');

            return $response;
        }

        $cacheKey = $config->getCacheKey();

        $cacheResponse = new Response();
        $this->cacheHelper->makeResponseCacheable($cacheResponse, $cacheKey, $config->getLastUpdateDate(), $immutableRoute);
        if ($cacheResponse->isNotModified($request)) {
            return $cacheResponse;
        }

        try {
            $stream = $this->getStream($config, $filename);
            $response = $this->getResponseFromStreamInterface($stream, $request);
        } catch (NotFoundException) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $response->headers->add([
            Headers::CONTENT_DISPOSITION => $config->getDisposition().'; '.HeaderUtils::toString(['filename' => $filename], ';'),
            Headers::CONTENT_TYPE => $config->getMimeType(),
        ]);
        if ($immutableRoute) {
            $response->headers->add([
                Headers::X_ROBOTS_TAG => Headers::X_ROBOTS_TAG_NOINDEX,
            ]);
        }

        $this->cacheHelper->makeResponseCacheable($response, $cacheKey, $config->getLastUpdateDate(), $immutableRoute);

        return $response;
    }

    /**
     * @param array<string, mixed> $configArray
     */
    public function configFactory(string $hash, array $configArray): Config
    {
        Json::normalize($configArray);
        $configHash = $this->storageManager->computeStringHash(Json::encode($configArray));

        return new Config($this->storageManager, $hash, $configHash, $configArray);
    }

    private function generateStream(Config $config): StreamInterface
    {
        $file = null;
        if (!$config->isCacheableResult()) {
            $file = $this->storageManager->getPublicImage('big-logo.png');
        } elseif ($config->getFilename()) {
            $file = $config->getFilename();
        }
        if ('image' === $config->getConfigType()) {
            $file = $this->generateImage($config, $file);
            $resource = \fopen($file->getFilename(), 'r');
            if (false === $resource) {
                throw new \Exception('It was not able to open the generated image');
            }
            $this->storageManager->saveCache($config, $file);

            return new Stream($resource);
        }

        if ('zip' === $config->getConfigType()) {
            return $this->generateZip($config);
        }

        $filename = $config->getFilename();
        if (null !== $filename) {
            return $this->getStreamFomFilename($filename);
        }

        throw new \Exception(\sprintf('not able to generate file for the config %s', $config->getConfigHash()));
    }

    private function generateImage(Config $config, string $filename = null): FileInterface
    {
        $image = new Image($config, $this->logger);

        $watermark = $config->getWatermark();
        if (null !== $watermark && $this->storageManager->head($watermark)) {
            $image->setWatermark($this->storageManager->getFile($watermark)->getFilename());
        }

        try {
            if ($filename) {
                $file = new LocalFile($filename);
            } else {
                $file = $this->storageManager->getFile($config->getAssetHash());
            }
            $generatedImage = $config->isSvg() ? $file : $image->generate($file->getFilename());
        } catch (\InvalidArgumentException) {
            $generatedImage = $image->generate($this->storageManager->getPublicImage('big-logo.png'));
        }

        return $generatedImage;
    }

    private function generateZip(Config $config): StreamInterface
    {
        $zip = new Zip($config);

        return $zip->generate();
    }

    private function getStreamFomFilename(string $filename): StreamInterface
    {
        $resource = \fopen($filename, 'r');
        if (false === $resource) {
            throw new NotFoundException($filename);
        }

        return new Stream($resource);
    }

    private function getStreamFromAsset(Config $config): StreamInterface
    {
        if (null !== $config->getFilename()) {
            return $this->getStreamFomFilename($config->getFilename());
        }

        try {
            return $this->storageManager->getStream($config->getAssetHash());
        } catch (NotFoundException) {
            throw new NotFoundHttpException(\sprintf('File %s not found', $config->getAssetHash()));
        }
    }

    public function getStream(Config $config, string $filename, bool $noCache = false): StreamInterface
    {
        if (null === $config->getCacheContext() && 'processor' !== $config->getAssetHash()) {
            return $this->getStreamFromAsset($config);
        }

        if (!$noCache) {
            $cache = $this->storageManager->readCache($config);
        }
        if (isset($cache)) {
            return $cache;
        }

        return $this->generateStream($config);
    }

    private function getResponseFromStreamInterface(StreamInterface $stream, Request $request): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($stream) {
            if ($stream->isSeekable() && $stream->tell() > 0) {
                $stream->rewind();
            }

            while (!$stream->eof()) {
                echo $stream->read(File::DEFAULT_CHUNK_SIZE);
            }
            $stream->close();
        });

        if (null === $fileSize = $stream->getSize()) {
            return $response;
        }
        $response->headers->set('Content-Length', \strval($fileSize));

        if ($stream->isSeekable()) {
            $response->headers->set('Accept-Ranges', $request->isMethodSafe() ? 'bytes' : 'none');
        }

        try {
            $streamRange = new StreamRange($request->headers, $fileSize);
        } catch (\RuntimeException) {
            return $response;
        }

        if (!$streamRange->isSatisfiable()) {
            $response->setStatusCode(StreamedResponse::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
            $response->headers->set('Content-Range', $streamRange->getContentRangeHeader());
        } elseif ($streamRange->isPartial()) {
            $response->setStatusCode(StreamedResponse::HTTP_PARTIAL_CONTENT);
            $response->headers->set('Content-Range', $streamRange->getContentRangeHeader());
            $response->headers->set('Content-Length', $streamRange->getContentLengthHeader());

            $response->setCallback(function () use ($stream, $streamRange) {
                $offset = $streamRange->getStart();
                $buffer = File::DEFAULT_CHUNK_SIZE;
                $stream->seek($offset);
                while (!$stream->eof() && ($offset = $stream->tell()) < $streamRange->getEnd()) {
                    if ($offset + $buffer > $streamRange->getEnd()) {
                        $buffer = $streamRange->getEnd() + 1 - $offset;
                    }
                    echo $stream->read($buffer);
                }
                $stream->close();
            });
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function overwriteMimeType(string $mimeType, array $config): string
    {
        switch ($config[EmsFields::ASSET_CONFIG_TYPE] ?? 'none') {
            case EmsFields::ASSET_CONFIG_TYPE_IMAGE:
                if ($mimeType && \preg_match('/image\/svg.*/', $mimeType)) {
                    return $mimeType;
                }
                if (EmsFields::ASSET_CONFIG_GIF_IMAGE_FORMAT === ($config[EmsFields::ASSET_CONFIG_IMAGE_FORMAT] ?? null)) {
                    return 'image/gif';
                }
                if (EmsFields::ASSET_CONFIG_BMP_IMAGE_FORMAT === ($config[EmsFields::ASSET_CONFIG_IMAGE_FORMAT] ?? null)) {
                    return 'image/bmp';
                }
                if (EmsFields::ASSET_CONFIG_WEBP_IMAGE_FORMAT === ($config[EmsFields::ASSET_CONFIG_IMAGE_FORMAT] ?? null)) {
                    return 'image/webp';
                }
                if ((0 === ($config[EmsFields::ASSET_CONFIG_QUALITY] ?? 0) && null === ($config[EmsFields::ASSET_CONFIG_IMAGE_FORMAT] ?? null)) || EmsFields::ASSET_CONFIG_PNG_IMAGE_FORMAT === ($config[EmsFields::ASSET_CONFIG_IMAGE_FORMAT] ?? null)) {
                    return 'image/png';
                }

                return 'image/jpeg';
            case EmsFields::ASSET_CONFIG_TYPE_ZIP:
                return 'application/zip';
        }

        return $mimeType;
    }

    /**
     * @param mixed[] $config
     */
    public function generateLocalImage(string $filename, array $config, bool $noCache = false): StreamInterface
    {
        $path = $this->locate($filename);
        $config = $this->localFileConfig($filename, $config);
        $stream = $this->storageManager->readCache($config);
        if (null !== $stream) {
            return $stream;
        }

        $file = $this->generateImage($config, $path);
        $resource = \fopen($file->getFilename(), 'r');
        if (false === $resource) {
            throw new \RuntimeException('It was not able to open the generated image');
        }

        return new Stream($resource);
    }

    /**
     * @param mixed[] $config
     */
    public function localFileConfig(string $filename, array $config): Config
    {
        $path = $this->locate($filename);

        return Config::forFile($this->storageManager, $path, $config);
    }

    private function locate(string $filename): string
    {
        $path = $this->fileLocator->locate($filename);
        if (!\is_string($path)) {
            throw new \RuntimeException(\sprintf('Unexpected multiple location to the file %s', $filename));
        }

        return $path;
    }

    public function getResponseFromArchive(Request $request, string $hash, string $path, int $maxAge, bool $extract, ?string $indexResource): Response
    {
        $streamWrapper = $this->storageManager->getStreamFromArchive($hash, $path, $extract, $indexResource);
        $response = $this->getResponseFromStreamInterface($streamWrapper->getStream(), $request);
        $response->headers->add([
            Headers::CONTENT_DISPOSITION => HeaderUtils::DISPOSITION_INLINE.'; '.HeaderUtils::toString(['filename' => \basename($path)], ';'),
            Headers::CONTENT_TYPE => $streamWrapper->getMimetype(),
        ]);
        $response->setCache([
            'etag' => \hash('sha1', \sprintf('Asset in archive: %s:%s', $hash, $path)),
            'max_age' => $maxAge,
            'public' => true,
            'private' => false,
        ]);

        return $response;
    }
}
