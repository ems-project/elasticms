<?php

declare(strict_types=1);

namespace App\CLI\Helper;

use EMS\Helpers\Standard\Text;
use Psr\Http\Message\StreamInterface;

class TikaWrapper extends ProcessWrapper
{
    private readonly string $tikaJar;

    private function __construct(StreamInterface $stream, string $option, string $cacheFolder, private readonly bool $trimWhiteSpaces = false, float $timeout = 3 * 60.0)
    {
        $this->tikaJar = \join(DIRECTORY_SEPARATOR, [$cacheFolder, 'tika.jar']);
        parent::__construct(['java', '-Djava.awt.headless=true', '-jar', $this->tikaJar, $option], $stream, $timeout);
    }

    public static function getLocale(StreamInterface $stream, string $cacheFolder, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--language', $cacheFolder, $trimWhiteSpaces);
    }

    public static function getHtml(StreamInterface $stream, string $cacheFolder): TikaWrapper
    {
        return new self($stream, '--html', $cacheFolder);
    }

    public static function getText(StreamInterface $stream, string $cacheFolder, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--text', $cacheFolder, $trimWhiteSpaces);
    }

    public static function getTextMain(StreamInterface $stream, string $cacheFolder, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--text-main', $cacheFolder, $trimWhiteSpaces);
    }

    public static function getMetadata(StreamInterface $stream, string $cacheFolder, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--metadata', $cacheFolder, $trimWhiteSpaces);
    }

    public static function getJsonMetadata(StreamInterface $stream, string $cacheFolder): TikaWrapper
    {
        return new self($stream, '--json', $cacheFolder);
    }

    public static function getDocumentType(StreamInterface $stream, string $cacheFolder): TikaWrapper
    {
        return new self($stream, '--detect', $cacheFolder, true);
    }

    public function getOutput(): string
    {
        if ($this->trimWhiteSpaces) {
            return Text::superTrim(parent::getOutput());
        }

        return parent::getOutput();
    }

    protected function initialize(): void
    {
        if (\file_exists($this->tikaJar)) {
            return;
        }
        \file_put_contents($this->tikaJar, \fopen('https://dlcdn.apache.org/tika/2.6.0/tika-app-2.6.0.jar', 'rb'));
    }
}
