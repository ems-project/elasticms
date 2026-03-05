<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit;

use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type InlineEditOptions array{
 *     document: DocumentInterface,
 *     path: string,
 *     element: string,
 *     attributes: array<string, scalar|null>
 * }
 */
class InlineEditConfigFactory
{
    /**
     * @param array<mixed> $options
     */
    public static function fromArray(array $options): InlineEditConfig
    {
        $options = self::resolveOptions($options);

        return new InlineEditConfig(
            document: $options['document'],
            path: $options['path'],
            element: $options['element'],
            attributes: $options['attributes'],
        );
    }

    /**
     * @param array<mixed> $options
     *
     * @return InlineEditOptions
     */
    private static function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'element' => 'div',
                'attributes' => [],
            ])
            ->setRequired(['document', 'path'])
            ->setAllowedTypes('element', 'string')
            ->setAllowedTypes('document', DocumentInterface::class)
            ->setAllowedTypes('path', 'string')
            ->setAllowedTypes('attributes', 'array')
            ->setNormalizer('attributes', static function (Options $options, array $value): array {
                foreach ($value as $key => $val) {
                    if (!\is_string($key) || (!\is_scalar($val) && null !== $val)) {
                        throw new \InvalidArgumentException('Attributes must be array<string, scalar|null>.');
                    }
                }

                return $value;
            });

        /** @var InlineEditOptions $config */
        $config = $resolver->resolve($options);

        return $config;
    }
}
