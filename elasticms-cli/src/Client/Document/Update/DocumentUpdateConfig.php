<?php

declare(strict_types=1);

namespace App\Client\Document\Update;

use App\Client\Data\Column\DataColumn;
use EMS\CommonBundle\Common\Standard\Json;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DocumentUpdateConfig
{
    /** @var DataColumn[] */
    public array $dataColumns;

    public string $updateContentType;
    public int $updateIndexEmsId;
    /** @var UpdateMap[] */
    public array $updateMapping;
    private ?string $collectionField;

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config)
    {
        $resolver = $this->getOptionsResolver();
        /** @var array{'update': array{'contentType': string, 'indexEmsId': int, 'mapping': UpdateMap[], collectionField: string|null}, 'dataColumns': DataColumn[]} $config */
        $config = $resolver->resolve($config);

        $this->dataColumns = $config['dataColumns'];

        $this->updateContentType = \strval($config['update']['contentType']);
        $this->updateIndexEmsId = \intval($config['update']['indexEmsId']);
        $this->updateMapping = $config['update']['mapping'];
        $this->collectionField = $config['update']['collectionField'];
    }

    public static function fromFile(string $filename): self
    {
        try {
            $fileContent = \file_get_contents($filename);
        } catch (\Throwable $e) {
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
            ->setDefault('update', function (OptionsResolver $updateResolver) {
                $updateResolver
                    ->setDefaults(['mapping' => [], 'collectionField' => null])
                    ->setRequired(['contentType', 'indexEmsId', 'mapping'])
                    ->setAllowedTypes('contentType', 'string')
                    ->setAllowedTypes('indexEmsId', 'int')
                    ->setAllowedTypes('collectionField', ['null', 'string'])
                    ->setNormalizer('mapping', fn (Options $options, array $value) => \array_map(fn ($map) => new UpdateMap($map), $value))
                ;
            })
            ->setNormalizer('dataColumns', fn (Options $options, array $value) => \array_map(function (array $column) {
                $class = DataColumn::TYPES[$column['type']] ?? false;

                if (!$class) {
                    throw new \RuntimeException(\sprintf('Invalid column type "%s", allowed type "%s"', $column['type'], \implode('|', \array_keys(DataColumn::TYPES))));
                }

                return new $class($column);
            }, $value))
        ;

        return $optionsResolver;
    }

    public function getCollectionField(): ?string
    {
        return $this->collectionField;
    }
}
