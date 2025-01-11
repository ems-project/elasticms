<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use Psr\Http\Message\StreamInterface;

class TikaJarPromise implements TikaPromiseInterface
{
    private readonly TikaWrapper $textWrapper;
    private readonly TikaWrapper $htmlWrapper;
    private readonly TikaWrapper $metaWrapper;
    private readonly TikaWrapper $languageWrapper;

    public function __construct(StreamInterface $stream)
    {
        $this->textWrapper = TikaWrapper::getText($stream);
        $this->htmlWrapper = TikaWrapper::getHtml($stream);
        $this->metaWrapper = TikaWrapper::getJsonMetadata($stream);
        $this->languageWrapper = TikaWrapper::getLanguage($stream);
    }

    #[\Override]
    public function startText(): void
    {
        $this->textWrapper->start();
    }

    #[\Override]
    public function getText(): string
    {
        return $this->textWrapper->getOutput();
    }

    #[\Override]
    public function startMeta(): void
    {
        $this->languageWrapper->start();
        $this->metaWrapper->start();
    }

    #[\Override]
    public function getMeta(): TikaMeta
    {
        return new TikaMeta([...$this->metaWrapper->getJson(), ...['language' => $this->languageWrapper->getOutput()]]);
    }

    #[\Override]
    public function startHtml(): void
    {
        $this->htmlWrapper->start();
    }

    #[\Override]
    public function getHtml(): string
    {
        return $this->htmlWrapper->getOutput();
    }
}
