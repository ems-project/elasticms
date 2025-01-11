<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use App\CLI\Helper\ProcessWrapper;
use Psr\Http\Message\StreamInterface;

class TikaWrapper extends ProcessWrapper
{
    private const string EMSCLI_TIKA_PATH = 'EMSCLI_TIKA_PATH';
    private readonly string $tikaJar;

    private function __construct(StreamInterface $stream, string $option, float $timeout = 3 * 60.0)
    {
        $this->tikaJar = \getenv(self::EMSCLI_TIKA_PATH) ?: '/opt/bin/tika-app.jar';
        parent::__construct(['java', '-Djava.awt.headless=true', '-jar', $this->tikaJar, $option], $stream, $timeout);
    }

    public static function getLanguage(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--language');
    }

    public static function getHtml(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--html');
    }

    public static function getText(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--text');
    }

    public static function getTextMain(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--text-main');
    }

    public static function getMetadata(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--metadata');
    }

    public static function getJsonMetadata(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--json');
    }

    public static function getDocumentType(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--detect');
    }

    #[\Override]
    protected function initialize(): void
    {
        if (!\file_exists($this->tikaJar)) {
            throw new \RuntimeException(\sprintf('Tika JAR not found at %s, you can also use the environment variable %s in order localise the Tika JAR', $this->tikaJar, self::EMSCLI_TIKA_PATH));
        }
    }
}
