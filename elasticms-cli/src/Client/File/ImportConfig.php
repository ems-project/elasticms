<?php

declare(strict_types=1);

namespace App\CLI\Client\File;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportConfig
{
    /**
     * @param array<string, mixed> $defaultData
     * @param array<string, mixed> $query
     * @param int[]                $excludeRows
     * @param array<int, array{
     *     'source': string,
     *     'target': string
     * }>                          $alignEnvironments
     */
    private function __construct(
        public array $defaultData = [],
        public bool $deleteMissingDocuments = false,
        public bool $lowercaseHeaders = false,
        public ?array $query = null,
        public ?string $delimiter = null,
        public ?string $encoding = null,
        public array $excludeRows = [],
        public ?string $excludeExpression = null,
        public bool $generateHash = false,
        public bool $generateOuuid = false,
        public ?string $ouuidExpression = "row['ouuid']",
        public ?string $ouuidVersionExpression = null,
        public ?string $ouuidPrefix = null,
        public array $alignEnvironments = [],
        public ?string $mimeType = null,
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
                'lowercase_headers' => false,
                'query' => null,
                'delimiter' => null,
                'encoding' => null,
                'exclude_rows' => [],
                'exclude_expression' => null,
                'generate_hash' => false,
                'generate_ouuid' => false,
                'ouuid_expression' => "row['ouuid']",
                'ouuid_version_expression' => null,
                'ouuid_prefix' => null,
                'align_environments' => [],
                'mime_type' => null,
            ])
            ->setAllowedTypes('delete_missing_documents', 'bool')
            ->setAllowedTypes('lowercase_headers', 'bool')
            ->setAllowedTypes('query', ['array', 'null'])
            ->setAllowedTypes('generate_hash', 'bool')
            ->setAllowedTypes('generate_ouuid', 'bool')
            ->setAllowedTypes('exclude_expression', ['string', 'null'])
            ->setAllowedTypes('ouuid_expression', ['string', 'null'])
            ->setAllowedTypes('ouuid_version_expression', ['string', 'null'])
            ->setAllowedTypes('ouuid_prefix', ['string', 'null'])
            ->setAllowedTypes('align_environments', ['array'])
            ->setAllowedTypes('mime_type', ['string', 'null'])
            ->setNormalizer('align_environments', function (OptionsResolver $resolver, array $value) {
                $alignEnvironment = new OptionsResolver();
                $alignEnvironment
                    ->setRequired(['source', 'target'])
                    ->setAllowedTypes('source', 'string')
                    ->setAllowedTypes('target', 'string');

                return \array_map($alignEnvironment->resolve(...), $value);
            })
        ;

        $options = $optionsResolver->resolve($config);

        return new self(
            defaultData: $options['default_data'],
            deleteMissingDocuments: $options['delete_missing_documents'],
            lowercaseHeaders: $options['lowercase_headers'],
            query: $options['query'],
            delimiter: $options['delimiter'],
            encoding: $options['encoding'],
            excludeRows: $options['exclude_rows'],
            excludeExpression: $options['exclude_expression'],
            generateHash: $options['generate_hash'],
            generateOuuid: $options['generate_ouuid'],
            ouuidExpression: $options['ouuid_expression'],
            ouuidVersionExpression: $options['ouuid_version_expression'],
            ouuidPrefix: $options['ouuid_prefix'],
            alignEnvironments: $options['align_environments'],
            mimeType: $options['mime_type'],
        );
    }
}
