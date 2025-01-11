<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Translation;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Dumper\YamlFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @implements \IteratorAggregate<TranslationFile>
 */
final class TranslationFiles implements \IteratorAggregate, \Countable
{
    /** @var TranslationFile[] */
    private array $files = [];

    private const string DIRECTORY = 'translations';

    public function __construct(string $directory)
    {
        $path = $directory.\DIRECTORY_SEPARATOR.self::DIRECTORY;

        if (!\file_exists($path)) {
            return;
        }
        foreach (Finder::create()->in($path)->files()->name('*.yaml') as $file) {
            $this->files[] = new TranslationFile($file);
        }
    }

    /**
     * @param MessageCatalogue[] $messageCatalogues
     */
    public static function build(string $directory, iterable $messageCatalogues): self
    {
        $path = $directory.\DIRECTORY_SEPARATOR.self::DIRECTORY;
        $dumper = new YamlFileDumper('yaml');

        foreach ($messageCatalogues as $messageCatalogue) {
            $dumper->dump($messageCatalogue, ['path' => $path, 'as_tree' => true, 'inline' => 5]);
        }

        return new self($directory);
    }

    #[\Override]
    public function count(): int
    {
        $count = 0;

        foreach ($this->files as $file) {
            $count += $file->count();
        }

        return $count;
    }

    /**
     * @return \ArrayIterator<int, TranslationFile>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->files);
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        $result = [];

        foreach ($this->files as $file) {
            foreach ($file->toArray() as $key => $label) {
                $result[$key]['key'] = $key;
                $result[$key]['label_'.$file->locale] = $label;
            }
        }

        return $result;
    }
}
