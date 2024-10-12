<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Archive implements \JsonSerializable
{
    /** @var array<string, ArchiveItem> */
    private array $files = [];
    private ?OptionsResolver $itemResolver = null;

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

    public static function fromStructure(string $structure, string $hashAlgo): self
    {
        $archive = new self($hashAlgo);
        $files = Json::decode($structure);
        foreach ($files as $file) {
            $item = $archive->parseFile($file);
            $archive->files[$item->getFilename()] = $item;
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

    public function getSize(): int
    {
        return \count($this->files);
    }

    /**
     * @return ArchiveItem[]
     */
    public function jsonSerialize(): array
    {
        \ksort($this->files);

        return $this->files;
    }

    /**
     * @param mixed[] $file
     */
    private function parseFile(array $file): ArchiveItem
    {
        $resolved = $this->resolveFile($file);

        return new ArchiveItem(
            $resolved[ArchiveItem::FILENAME],
            $resolved[ArchiveItem::TYPE],
            $resolved[ArchiveItem::HASH],
        );
    }

    /**
     * @param  mixed[]                                             $file
     * @return array{filename: string, hash: string, type: string}
     */
    private function resolveFile(array $file): array
    {
        if (null === $this->itemResolver) {
            $this->itemResolver = new OptionsResolver();
            $this->itemResolver
                ->setRequired([ArchiveItem::FILENAME, ArchiveItem::HASH, ArchiveItem::TYPE])
                ->setAllowedTypes(ArchiveItem::FILENAME, 'string')
                ->setAllowedTypes(ArchiveItem::HASH, 'string')
                ->setAllowedTypes(ArchiveItem::TYPE, 'string');
        }
        /** @var array{filename: string, hash: string, type: string} $resolved */
        $resolved = $this->itemResolver->resolve($file);

        return $resolved;
    }
}
