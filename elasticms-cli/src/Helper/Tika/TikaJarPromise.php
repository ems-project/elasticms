<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use Psr\Http\Message\StreamInterface;

class TikaJarPromise implements TikaPromiseInterface
{
    private TikaWrapper $textWrapper;
    private TikaWrapper $htmlWrapper;
    private TikaWrapper $metaWrapper;
    private TikaWrapper $languageWrapper;

    public function __construct(StreamInterface $stream)
    {
        $this->textWrapper = TikaWrapper::getText($stream);
        $this->htmlWrapper = TikaWrapper::getHtml($stream);
        $this->metaWrapper = TikaWrapper::getJsonMetadata($stream);
        $this->languageWrapper = TikaWrapper::getLanguage($stream);
    }

    public function startText(): void
    {
        $this->textWrapper->start();
    }

    public function getText(): string
    {
        return $this->textWrapper->getOutput();
    }

    public function startMeta(): void
    {
        $this->languageWrapper->start();
        $this->metaWrapper->start();
    }

    public function getMeta(): TikaMeta
    {
        return new TikaMeta([...$this->metaWrapper->getJson(), ...['language' => $this->languageWrapper->getOutput()]]);
    }

    public function startHtml(): void
    {
        $this->htmlWrapper->start();
    }

    public function getHtml(): string
    {
        return $this->htmlWrapper->getOutput();
    }
}
