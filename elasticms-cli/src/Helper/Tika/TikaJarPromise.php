<?php

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
        $this->textWrapper = TikaWrapper::getText($stream, \sys_get_temp_dir());
        $this->htmlWrapper = TikaWrapper::getHtml($stream, \sys_get_temp_dir());
        $this->metaWrapper = TikaWrapper::getJsonMetadata($stream, \sys_get_temp_dir());
        $this->languageWrapper = TikaWrapper::getLanguage($stream, \sys_get_temp_dir());
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
