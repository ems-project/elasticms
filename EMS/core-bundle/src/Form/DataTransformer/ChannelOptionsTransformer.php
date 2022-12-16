<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<mixed, mixed>
 */
final class ChannelOptionsTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        $searchConfig = $this->jsonFormat($value, 'searchConfig');
        $attributes = $this->jsonFormat($value, 'attributes');

        return [
            'searchConfig' => $searchConfig,
            'entryPath' => $value['entryPath'] ?? null,
            'attributes' => $attributes,
        ];
    }

    public function reverseTransform($value)
    {
        return [
            'searchConfig' => $value['searchConfig'] ?? '',
            'entryPath' => $value['entryPath'] ?? '',
            'attributes' => $value['attributes'] ?? '',
        ];
    }

    /**
     * @param array<string, mixed> $value
     */
    private function jsonFormat(array $value, string $attribute): string
    {
        try {
            $formatted = \json_decode($value[$attribute] ?? '', true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return $value[$attribute] ?? '';
        }
        $reEncoded = \json_encode($formatted, JSON_PRETTY_PRINT);

        return false === $reEncoded ? '' : $reEncoded;
    }
}
