<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Factory;

use EMS\CommonBundle\Storage\Service\StorageInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFactory implements StorageFactoryInterface
{
    protected function getDefaultOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                self::STORAGE_CONFIG_TYPE => null,
                self::STORAGE_CONFIG_USAGE => StorageInterface::STORAGE_USAGE_CACHE_ATTRIBUTE,
                self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT => '0',
            ])
            ->setAllowedTypes(self::STORAGE_CONFIG_TYPE, 'string')
            ->setAllowedTypes(self::STORAGE_CONFIG_USAGE, 'string')
            ->setAllowedTypes(self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT, 'string')
            ->setRequired(self::STORAGE_CONFIG_TYPE)
            ->setAllowedValues(self::STORAGE_CONFIG_USAGE, \array_keys(StorageInterface::STORAGE_USAGES))
            ->setNormalizer(self::STORAGE_CONFIG_USAGE, self::usageResolver())
            ->setNormalizer(self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT, self::hotSynchronizeLimitResolver())
        ;

        return $resolver;
    }

    protected function usageResolver(): \Closure
    {
        return function (Options $options, string $value): int {
            if (isset(StorageInterface::STORAGE_USAGES[$value])) {
                return StorageInterface::STORAGE_USAGES[$value];
            }
            throw new \RuntimeException(\sprintf('Unsupported storage usage value %s', $value));
        };
    }

    protected function hotSynchronizeLimitResolver(): \Closure
    {
        return function (Options $options, string $value): int {
            $matches = [];
            \preg_match('/^\s*(?P<number>\d+)\s*(?:(?P<prefix>[kmgt]?)b?)?\s*$/i', $value, $matches);

            $limit = \intval($matches['number'] ?? 0);
            $prefix = \strtolower(\strval($matches['prefix'] ?? ''));
            switch ($prefix) {
                case 't': $limit *= 1024;
                    // no break
                case 'g': $limit *= 1024;
                    // no break
                case 'm': $limit *= 1024;
                    // no break
                case 'k': $limit *= 1024;
            }

            return $limit;
        };
    }
}
