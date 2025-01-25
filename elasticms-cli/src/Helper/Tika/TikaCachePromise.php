<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Json;
use Psr\Http\Message\StreamInterface;

class TikaCachePromise implements TikaPromiseInterface
{
    private const string TYPE_TEXT = 'text';
    private const string TYPE_META = 'meta';
    private const string TYPE_HTML = 'html';
    private readonly string $hash;

    public function __construct(StreamInterface $stream, private readonly string $cacheFolder, private readonly TikaPromiseInterface $promise)
    {
        if (0 !== $stream->tell()) {
            $stream->rewind();
        }
        $hashContext = \hash_init('sha1');
        while (!$stream->eof()) {
            \hash_update($hashContext, $stream->read(1024 * 1024));
        }

        $this->hash = \hash_final($hashContext);
    }

    #[\Override]
    public function startText(): void
    {
        if (!$this->isCached(self::TYPE_TEXT)) {
            $this->promise->startText();
        }
    }

    #[\Override]
    public function getText(): string
    {
        if ($this->isCached(self::TYPE_TEXT)) {
            return $this->getCache(self::TYPE_TEXT);
        }
        $text = $this->promise->getText();
        $this->putCache(self::TYPE_TEXT, $text);

        return $text;
    }

    #[\Override]
    public function startMeta(): void
    {
        if (!$this->isCached(self::TYPE_META)) {
            $this->promise->startMeta();
        }
    }

    #[\Override]
    public function getMeta(): TikaMeta
    {
        if ($this->isCached(self::TYPE_META)) {
            return new TikaMeta(Json::decode($this->getCache(self::TYPE_META)));
        }
        $meta = $this->promise->getMeta();
        $this->putCache(self::TYPE_META, Json::encode($meta->getMeta()));

        return $meta;
    }

    #[\Override]
    public function startHtml(): void
    {
        if (!$this->isCached(self::TYPE_HTML)) {
            $this->promise->startHtml();
        }
    }

    #[\Override]
    public function getHtml(): string
    {
        if ($this->isCached(self::TYPE_HTML)) {
            return $this->getCache(self::TYPE_HTML);
        }
        $html = $this->promise->getHtml();
        $this->putCache(self::TYPE_HTML, $html);

        return $html;
    }

    private function isCached(string $type): bool
    {
        try {
            return \is_file($this->filename($type));
        } catch (\Throwable) {
            return false;
        }
    }

    private function getCache(string $type): string
    {
        $contents = \file_get_contents($this->filename($type));
        if (false === $contents) {
            throw new \RuntimeException('Contents not found');
        }

        return $contents;
    }

    private function filename(string $type): string
    {
        return \implode(DIRECTORY_SEPARATOR, [$this->cacheFolder, 'Tika_Cache', $type, $this->hash]);
    }

    private function putCache(string $type, string $contents): void
    {
        $filename = $this->filename($type);
        $dirname = \dirname($filename);
        if (!\file_exists($dirname)) {
            \mkdir($dirname, 0o755, true);
        }
        File::putContents($filename, $contents);
    }
}
