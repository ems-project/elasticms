<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use EMS\Helpers\Standard\Json;
use Psr\Http\Message\StreamInterface;

class TikaCachePromise implements TikaPromiseInterface
{
    private string $hash;

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

    public function startText(): void
    {
        if (!$this->isCached('text')) {
            $this->promise->startText();
        }
    }

    public function getText(): string
    {
        if ($this->isCached('text')) {
            return $this->getCache('text');
        }
        $text = $this->promise->getText();
        $this->putCache('text', $text);

        return $text;
    }

    public function startMeta(): void
    {
        if (!$this->isCached('meta')) {
            $this->promise->startMeta();
        }
    }

    public function getMeta(): TikaMeta
    {
        if ($this->isCached('meta')) {
            return new TikaMeta(Json::decode($this->getCache('meta')));
        }
        $meta = $this->promise->getMeta();
        $this->putCache('meta', Json::encode($meta->getMeta()));

        return $meta;
    }

    public function startHtml(): void
    {
        if (!$this->isCached('html')) {
            $this->promise->startHtml();
        }
    }

    public function getHtml(): string
    {
        if ($this->isCached('html')) {
            return $this->getCache('html');
        }
        $html = $this->promise->getHtml();
        $this->putCache('html', $html);

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
        if (!file_exists($dirname)) {
            \mkdir($dirname, 0755, true);
        }
        \file_put_contents($filename, $contents);
    }
}
