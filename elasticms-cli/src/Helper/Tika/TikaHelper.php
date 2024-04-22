<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;

class TikaHelper
{
    private readonly MimeTypesInterface $mimeTypes;

    private function __construct(private readonly ?string $tikaBaseUrl, private readonly ?string $tikaCacheFolder)
    {
        $this->mimeTypes = new MimeTypes();
    }

    public static function initTikaJar(?string $tikaCacheFolder = null): TikaHelper
    {
        return new self(null, $tikaCacheFolder);
    }

    public static function initTikaServer(string $tikaBaseUrl, ?string $tikaCacheFolder = null): TikaHelper
    {
        return new self($tikaBaseUrl, $tikaCacheFolder);
    }

    public function extractFromFile(string $filename): TikaPromiseInterface
    {
        if (!\file_exists($filename)) {
            throw new \RuntimeException(\sprintf('File %s does not exist', $filename));
        }
        $resource = \fopen($filename, 'r');
        if (false === $resource) {
            throw new \RuntimeException(\sprintf('Not able to read the file %s', $filename));
        }
        $stream = new Stream($resource);
        $mimeType = $this->mimeTypes->guessMimeType($filename);

        return $this->extract($stream, $mimeType);
    }

    public function extract(StreamInterface $stream, ?string $mimeType): TikaPromiseInterface
    {
        if ($this->tikaBaseUrl) {
            $promise = new TikaServerPromise($this->tikaBaseUrl, $stream, $mimeType);
        } else {
            $promise = new TikaJarPromise($stream);
        }

        if (null !== $this->tikaCacheFolder) {
            $promise = new TikaCachePromise($stream, $this->tikaCacheFolder, $promise);
        }

        return $promise;
    }
}
