<?php

declare(strict_types=1);

namespace App\CLI\Client\File;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FileReaderImportConfig
{
    private function __construct(
        public bool $generateHash = false,
        public bool $deleteMissingDocuments = false,
        public ?string $ouuidExpression = "row['ouuid']",
        public ?string $encoding = null
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
                'generateHash' => false,
                'deleteMissingDocuments' => false,
                'ouuidExpression' => 'row[\'ouuid\']',
                'encoding' => null,
            ])
            ->setAllowedTypes('generateHash', 'bool')
            ->setAllowedTypes('deleteMissingDocuments', 'bool')
            ->setAllowedTypes('ouuidExpression', ['string', 'null'])
        ;

        $config = $optionsResolver->resolve($config);

        return new self(
            generateHash: $config['generateHash'],
            deleteMissingDocuments: $config['deleteMissingDocuments'],
            ouuidExpression: $config['ouuidExpression'],
            encoding: $config['encoding'],
        );
    }
}
