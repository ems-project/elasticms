<?php

declare(strict_types=1);

namespace App\CLI\Client\File;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FileReaderImportConfig
{
    /**
     * @param array<string, mixed> $defaultData
     */
    private function __construct(
        public bool $generateHash = false,
        public bool $deleteMissingDocuments = false,
        public ?string $delimiter = null,
        public array $defaultData = [],
        public ?string $ouuidExpression = "row['ouuid']",
        public ?string $encoding = null,
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
                'delimiter' => null,
                'default_data' => [],
                'delete_missing_documents' => false,
                'encoding' => null,
                'generate_hash' => false,
                'ouuid_expression' => 'row[\'ouuid\']',
            ])
            ->setAllowedTypes('delete_missing_documents', 'bool')
            ->setAllowedTypes('generate_hash', 'bool')
            ->setAllowedTypes('ouuid_expression', ['string', 'null'])
        ;

        $options = $optionsResolver->resolve($config);

        return new self(
            generateHash: $options['generate_hash'],
            deleteMissingDocuments: $options['delete_missing_documents'],
            delimiter: $options['delimiter'],
            defaultData: $options['default_data'],
            ouuidExpression: $options['ouuid_expression'],
            encoding: $options['encoding'],
        );
    }
}
