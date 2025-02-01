<?php

declare(strict_types=1);

namespace App\CLI\Client\Document\Update;

use App\CLI\Client\Data\Column\DataColumn;
use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Json;
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
    private readonly ?string $collectionField;

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config)
    {
        $resolver = $this->getOptionsResolver();
        /** @var array{'update': array{'contentType': string, 'indexEmsId': int, 'mapping': UpdateMap[], collectionField: string|null}, 'dataColumns': DataColumn[]} $config */
        $config = $resolver->resolve($config);

        $this->dataColumns = $config['dataColumns'];

        $this->updateContentType = (string) $config['update']['contentType'];
        $this->updateIndexEmsId = (int) $config['update']['indexEmsId'];
        $this->updateMapping = $config['update']['mapping'];
        $this->collectionField = $config['update']['collectionField'];
    }

    public static function fromFile(string $filename): self
    {
        $fileContent = File::fromFilename($filename)->getContents();

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
