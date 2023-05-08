<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use App\CLI\Helper\ProcessWrapper;
use EMS\Helpers\Standard\Text;
use Psr\Http\Message\StreamInterface;

class TikaWrapper extends ProcessWrapper
{
    private const EMSCLI_TIKA_PATH = 'EMSCLI_TIKA_PATH';
    private readonly string $tikaJar;

    private function __construct(StreamInterface $stream, string $option, private readonly bool $trimWhiteSpaces = false, float $timeout = 3 * 60.0)
    {
        $this->tikaJar = \getenv(self::EMSCLI_TIKA_PATH) ?: '/opt/bin/tika.jar';
        parent::__construct(['java', '-Djava.awt.headless=true', '-jar', $this->tikaJar, $option], $stream, $timeout);
    }

    public static function getLanguage(StreamInterface $stream, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--language', $trimWhiteSpaces);
    }

    public static function getHtml(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--html');
    }

    public static function getText(StreamInterface $stream, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--text', $trimWhiteSpaces);
    }

    public static function getTextMain(StreamInterface $stream, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--text-main', $trimWhiteSpaces);
    }

    public static function getMetadata(StreamInterface $stream, bool $trimWhiteSpaces = true): TikaWrapper
    {
        return new self($stream, '--metadata', $trimWhiteSpaces);
    }

    public static function getJsonMetadata(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--json');
    }

    public static function getDocumentType(StreamInterface $stream): TikaWrapper
    {
        return new self($stream, '--detect', true);
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
        if (!\file_exists($this->tikaJar)) {
            throw new \RuntimeException(\sprintf('Tika JAR not found at %s, you can also use the environment variable %s in order localise the Tika JAR', $this->tikaJar, self::EMSCLI_TIKA_PATH));
        }
    }
}
