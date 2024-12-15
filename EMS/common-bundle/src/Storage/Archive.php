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

    public static function fromDirectory(string $directory, string $hashAlgo, ?callable $callback = null): self
    {
        $archive = new self($hashAlgo);
        $finder = new Finder();
        $finder->files()->in($directory);

        if (!$finder->hasResults()) {
            throw new \RuntimeException('The directory is empty');
        }

        $maxSteps = $finder->count();
        $counter = 0;
        foreach ($finder as $file) {
            $archive->addFile($file);
            if (null !== $callback) {
                $callback($maxSteps, ++$counter);
            }
        }

        return $archive;
    }

    public static function fromStructure(string $structure, string $hashAlgo): self
    {
        $archive = new self($hashAlgo);
        $files = Json::decode($structure);
        foreach ($files as $file) {
            $item = $archive->parseFile($file);
            $archive->files[$item->filename] = $item;
        }

        return $archive;
    }

    /**
     * @return iterable<string>
     */
    public function getHashes(): iterable
    {
        foreach ($this->files as $file) {
            yield $file->hash;
        }
    }

    private function addFile(SplFileInfo $file): void
    {
        $hash = Type::string(\hash_file($this->hashAlgo, $file->getPathname()));
        $type = MimeTypeHelper::getInstance()->guessMimeType($file->getPathname());
        $size = Type::integer($file->getSize());
        $this->files[$file->getRelativePathname()] = new ArchiveItem($file->getRelativePathname(), $type, $size, $hash);
    }

    public function getFirstFileByHash(mixed $hash): ArchiveItem
    {
        foreach ($this->files as $file) {
            if ($hash === $file->hash) {
                return $file;
            }
        }
        throw new \RuntimeException(\sprintf('File with hash %s not found', $hash));
    }

    public function getCount(): int
    {
        return \count($this->files);
    }

    /**
     * @return ArchiveItem[]
     */
    public function jsonSerialize(): array
    {
        \ksort($this->files);

        return \array_values($this->files);
    }

    public function getByPath(string $path): ?ArchiveItem
    {
        return $this->files[$path] ?? null;
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
            $resolved[ArchiveItem::SIZE],
            $resolved[ArchiveItem::HASH],
        );
    }

    /**
     * @return iterable<ArchiveItem>
     */
    public function iterator(): iterable
    {
        foreach ($this->files as $path => $file) {
            yield $path => $file;
        }
    }

    /**
     * @param  mixed[]                                                        $file
     * @return array{filename: string, hash: string, type: string, size: int}
     */
    private function resolveFile(array $file): array
    {
        if (null === $this->itemResolver) {
            $this->itemResolver = new OptionsResolver();
            $this->itemResolver
                ->setRequired([ArchiveItem::FILENAME, ArchiveItem::HASH, ArchiveItem::TYPE, ArchiveItem::SIZE])
                ->setAllowedTypes(ArchiveItem::FILENAME, 'string')
                ->setAllowedTypes(ArchiveItem::HASH, 'string')
                ->setAllowedTypes(ArchiveItem::TYPE, 'string')
                ->setAllowedTypes(ArchiveItem::SIZE, 'int');
        }
        /** @var array{filename: string, hash: string, type: string, size: int} $resolved */
        $resolved = $this->itemResolver->resolve($file);

        return $resolved;
    }

    private function containsByHash(string $hash): bool
    {
        foreach ($this->files as $file) {
            if ($hash === $file->hash) {
                return true;
            }
        }

        return false;
    }

    public function diff(?Archive $otherArchive): self
    {
        if (null === $otherArchive) {
            return $this;
        }
        $newArchive = new self($this->hashAlgo);
        foreach ($this->files as $file) {
            if (null !== $otherArchive && $otherArchive->containsByHash($file->hash)) {
                continue;
            }
            $newArchive->addArchiveItem($file);
        }

        return $newArchive;
    }

    private function addArchiveItem(ArchiveItem $file): void
    {
        $this->files[$file->filename] = $file;
    }

    public function skip(int $skip): self
    {
        if ($skip <= 0) {
            return $this;
        }
        $newArchive = new self($this->hashAlgo);
        $counter = 0;
        foreach ($this->files as $file) {
            if ($counter++ < $skip) {
                continue;
            }
            $newArchive->addArchiveItem($file);
        }

        return $newArchive;
    }
}
