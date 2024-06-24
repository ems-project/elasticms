<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use EMS\Helpers\Standard\Type;
use Symfony\Component\Mime\MimeTypes;

class File
{
    public string $name;
    public string $extension;
    public string $mimeType;
    public int $size;

    public const DEFAULT_CHUNK_SIZE = 4 * 1024 * 1024;

    public function __construct(private readonly \SplFileInfo $file)
    {
        $this->name = $this->file->getFilename();
        $this->extension = $this->file->getExtension();
        $this->size = Type::integer($this->file->getSize());
        $this->mimeType = MimeTypes::getDefault()->guessMimeType($file->getPathname()) ?? 'application/octet-stream';
    }

    public static function fromFilename(string $filename): self
    {
        return new self(new \SplFileInfo($filename));
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
        $realPath = $this->file->getRealPath();

        if (false === $handle = \fopen($realPath, 'r')) {
            throw new \RuntimeException(\sprintf('Unexpected error while opening file %s', $realPath));
        }

        if ($fromByte > 0) {
            if (0 !== \fseek($handle, $fromByte)) {
                throw new \RuntimeException(\sprintf('Unexpected error while seeking the file pointer at position %s', $fromByte));
            }
        }

        while (!\feof($handle)) {
            $chunk = '';
            while (!\feof($handle) && \strlen($chunk) < $chunkSize) {
                $length = $chunkSize - \strlen($chunk);
                if ($length > 0) {
                    $chunk .= \fread($handle, $length);
                }
            }

            yield $chunk;
        }
        \fclose($handle);
    }
}
