<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use EMS\Helpers\Html\MimeTypes as MimeTypeHeader;
use EMS\Helpers\Standard\Type;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypes;

class File
{
    public string $name;
    public string $extension;
    public string $mimeType;
    public int $size;

    final public const DEFAULT_CHUNK_SIZE = 5 * 1024 * 1024;

    public function __construct(private readonly \SplFileInfo $file)
    {
        $this->name = $this->file->getFilename();
        $this->extension = $this->file->getExtension();
        $this->size = Type::integer($this->file->getSize());
        $this->mimeType = MimeTypes::getDefault()->guessMimeType($file->getPathname()) ?? MimeTypeHeader::APPLICATION_OCTET_STREAM->value;
    }

    public static function fromFilename(string $filename): self
    {
        if (!\file_exists($filename)) {
            throw new \RuntimeException(\sprintf('File "%s" does not exits', $filename));
        }

        return new self(new \SplFileInfo($filename));
    }

    public static function putContents(string $filename, string $contents): void
    {
        if (false === \file_put_contents($filename, $contents)) {
            throw new \RuntimeException(\sprintf('Unexpected false result on file_put_contents for file %s', $filename));
        }
    }

    public static function saveStreamedResponse(StreamedResponse $response, string $filename, int $chunkSize = self::DEFAULT_CHUNK_SIZE): void
    {
        $file = \fopen($filename, 'wb');

        if (false === $file) {
            throw new \RuntimeException('Unable to open the output file.');
        }

        \ob_start(static function (string $chunk) use ($file): string {
            $length = \strlen($chunk);
            $offset = 0;

            while ($offset < $length) {
                $written = \fwrite($file, \substr($chunk, $offset));

                if (false === $written || 0 === $written) {
                    throw new \RuntimeException('Unable to write to the output file.');
                }

                $offset += $written;
            }

            return '';
        }, $chunkSize);

        try {
            $response->sendContent();
        } finally {
            try {
                \ob_end_flush();
            } finally {
                \fclose($file);
            }
        }
    }

    public function getContents(): string
    {
        if (false === $contents = \file_get_contents($this->file->getRealPath())) {
            throw new \RuntimeException(\sprintf('Could not open file "%s"', $this->file->getRealPath()));
        }

        return $contents;
    }

    /**
     * @return iterable<string>
     */
    public function chunk(int $fromByte, int $chunkSize = self::DEFAULT_CHUNK_SIZE): iterable
    {
        $handle = $this->getHandler();

        if ($fromByte > 0 && 0 !== \fseek($handle, $fromByte)) {
            throw new \RuntimeException(\sprintf('Unexpected error while seeking the file pointer at position %s', $fromByte));
        }
        if ($chunkSize < 1) {
            throw new \RuntimeException(\sprintf('Unexpected chunk size %d', $chunkSize));
        }

        while (!\feof($handle)) {
            $chunk = \fread($handle, $chunkSize);
            if (false === $chunk) {
                throw new \RuntimeException('Unexpected false chunk');
            }
            yield $chunk;
        }
        \fclose($handle);
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getStream(): StreamInterface
    {
        $handle = $this->getHandler();

        return new Stream($handle);
    }

    /**
     * @return resource
     */
    private function getHandler()
    {
        $realPath = $this->file->getRealPath();

        if (false === $handle = \fopen($realPath, 'r')) {
            throw new \RuntimeException(\sprintf('Unexpected error while opening file %s', $realPath));
        }

        return $handle;
    }
}
