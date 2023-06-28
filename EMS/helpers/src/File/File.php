<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use EMS\Helpers\Standard\Type;
use Symfony\Component\Mime\MimeTypes;

class File
{
    public string $name;
    public string $mimeType;
    public int $size;

    public const DEFAULT_CHUNK_SIZE = 5 * 1024 * 1024;

    private function __construct(private readonly \SplFileInfo $file)
    {
        $this->name = $this->file->getFilename();
        $this->size = Type::integer($this->file->getSize());
        $this->mimeType = MimeTypes::getDefault()->guessMimeType($file->getPathname()) ?? 'application/octet-stream';
    }

    public static function fromFilename(string $filename): self
    {
        return new self(new \SplFileInfo($filename));
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
                $chunk .= \fread($handle, $chunkSize - \strlen($chunk));
            }

            yield $chunk;
        }
        \fclose($handle);
    }
}
