<?php

declare(strict_types=1);

namespace App\CLI\Client\File;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FileReaderImportConfig
{
    /**
     * @param array<string, mixed> $defaultData
     * @param int[]                $excludeRows
     */
    private function __construct(
        public array $defaultData = [],
        public bool $deleteMissingDocuments = false,
        public ?string $delimiter = null,
        public ?string $encoding = null,
        public array $excludeRows = [],
        public bool $generateHash = false,
        public bool $generateOuuid = false,
        public ?string $ouuidExpression = "row['ouuid']",
        public ?string $ouuidPrefix = null,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function createFromArray(array $config): self
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefaults([
                'default_data' => [],
                'delete_missing_documents' => false,
                'delimiter' => null,
                'encoding' => null,
                'exclude_rows' => [],
                'generate_hash' => false,
                'generate_ouuid' => false,
                'ouuid_expression' => 'row[\'ouuid\']',
                'ouuid_prefix' => null,
            ])
            ->setAllowedTypes('delete_missing_documents', 'bool')
            ->setAllowedTypes('generate_hash', 'bool')
            ->setAllowedTypes('generate_ouuid', 'bool')
            ->setAllowedTypes('ouuid_expression', ['string', 'null'])
            ->setAllowedTypes('ouuid_prefix', ['string', 'null'])
        ;

        $options = $optionsResolver->resolve($config);

        return new self(
            defaultData: $options['default_data'],
            deleteMissingDocuments: $options['delete_missing_documents'],
            delimiter: $options['delimiter'],
            encoding: $options['encoding'],
            excludeRows: $options['exclude_rows'],
            generateHash: $options['generate_hash'],
            generateOuuid: $options['generate_ouuid'],
            ouuidExpression: $options['ouuid_expression'],
            ouuidPrefix: $options['ouuid_prefix'],
        );
    }
}
