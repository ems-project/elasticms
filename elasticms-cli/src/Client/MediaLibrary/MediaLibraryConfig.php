<?php

declare(strict_types=1);

namespace App\CLI\Client\MediaLibrary;

use App\CLI\Client\Data\Column\DataColumn;
use EMS\Helpers\Standard\Json;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class MediaLibraryConfig
{
    /** @var DataColumn[] */
    public array $dataColumns;

    public string $xlsPath;
    /** @var MediaLibraryMap[] */
    public array $mediaLibraryMapping;
    private readonly ?string $collectionField;

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config)
    {
        $resolver = $this->getOptionsResolver();
        /** @var array{'mediaLibrary': array{'xlsPath': string, 'mapping': MediaLibraryMap[], collectionField: string|null}, 'dataColumns': DataColumn[]} $config */
        $config = $resolver->resolve($config);

        $this->dataColumns = $config['dataColumns'];

        $this->xlsPath = \strval($config['mediaLibrary']['xlsPath']);
        $this->mediaLibraryMapping = $config['mediaLibrary']['mapping'];
        $this->collectionField = $config['mediaLibrary']['collectionField'];
    }

    public static function fromFile(string $filename): self
    {
        try {
            $fileContent = \file_get_contents($filename);
        } catch (\Throwable) {
            $fileContent = false;
        }

        if (false === $fileContent) {
            throw new \RuntimeException(\sprintf('Could not read config file from %s', $filename));
        }

        return new self(Json::decode($fileContent));
    }

    private function getOptionsResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefaults([
                'dataColumns' => [],
            ])
            ->setDefault('mediaLibrary', function (OptionsResolver $updateResolver) {
                $updateResolver
                    ->setDefaults(['mapping' => [], 'collectionField' => null])
                    ->setRequired(['xlsPath', 'mapping'])
                    ->setAllowedTypes('xlsPath', 'string')
                    ->setAllowedTypes('collectionField', ['null', 'string'])
                    ->setNormalizer('mapping', fn (Options $options, array $value) => \array_map(fn ($map) => new MediaLibraryMap($map), $value));
            });

        return $optionsResolver;
    }

    public function getCollectionField(): ?string
    {
        return $this->collectionField;
    }

    public function getFolderColumn(): ?MediaLibraryMap
    {
        return $this->getColumnByAccessor('isFolder');
    }

    public function getFilenameColumn(): ?MediaLibraryMap
    {
        return $this->getColumnByAccessor('isFilename');
    }

    private function getColumnByAccessor(string $accessor): ?MediaLibraryMap
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($this->mediaLibraryMapping as $mediaMap) {
            if ($propertyAccessor->getValue($mediaMap, $accessor)) {
                return $mediaMap;
            }
        }

        return null;
    }
}
