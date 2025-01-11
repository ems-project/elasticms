<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use App\CLI\Helper\AsyncResponse;
use Psr\Http\Message\StreamInterface;

class TikaServerPromise implements TikaPromiseInterface
{
    private AsyncResponse $textRequest;
    private readonly TikaClient $tikaClient;
    private AsyncResponse $htmlRequest;
    private AsyncResponse $metaRequest;

    public function __construct(string $serverBaseUrl, private readonly StreamInterface $stream, private readonly ?string $mimeType)
    {
        $this->tikaClient = new TikaClient($serverBaseUrl);
    }

    #[\Override]
    public function startText(): void
    {
        $this->textRequest = $this->tikaClient->text($this->stream, $this->mimeType);
    }

    #[\Override]
    public function getText(): string
    {
        return $this->textRequest->getContent();
    }

    #[\Override]
    public function startMeta(): void
    {
        $this->metaRequest = $this->tikaClient->meta($this->stream, $this->mimeType);
    }

    #[\Override]
    public function getMeta(): TikaMeta
    {
        return new TikaMeta($this->metaRequest->getJson());
    }

    #[\Override]
    public function startHtml(): void
    {
        $this->htmlRequest = $this->tikaClient->html($this->stream, $this->mimeType);
    }

    #[\Override]
    public function getHtml(): string
    {
        return $this->htmlRequest->getContent();
    }
}
