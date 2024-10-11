<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Archive
{
    /** @var array<string, ArchiveItem> */
    private array $files = [];

    public function __construct(private readonly string $hashAlgo)
    {
    }

    public static function fromDirectory(string $directory, string $hashAlgo): self
    {
        $archive = new self($hashAlgo);
        $finder = new Finder();
        $finder->files()->in($directory);

        if (!$finder->hasResults()) {
            throw new \RuntimeException('The directory is empty');
        }

        foreach ($finder as $file) {
            $archive->addFile($file);
        }

        return $archive;
    }

    /**
     * @return iterable<string>
     */
    public function getHashes(): iterable
    {
        foreach ($this->files as $file) {
            yield $file->getHash();
        }
    }

    private function addFile(SplFileInfo $file): void
    {
        $hash = Type::string(\hash_file($this->hashAlgo, $file->getPathname()));
        $type = MimeTypeHelper::getInstance()->guessMimeType($file->getPathname());
        $this->files[$file->getRelativePathname()] = new ArchiveItem($file->getRelativePathname(), $type, $hash);
    }

    public function getFirstFileByHash(mixed $hash): ArchiveItem
    {
        foreach ($this->files as $file) {
            if ($hash === $file->getHash()) {
                return $file;
            }
        }
        throw new \RuntimeException(\sprintf('File with hash %s not found', $hash));
    }
}
