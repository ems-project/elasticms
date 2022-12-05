<?php

declare(strict_types=1);

namespace App\CLI\Client\Data\Column;

use App\CLI\Client\Data\Data;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class DataColumn
{
    final public const TYPES = [
        'businessId' => DataColumnBusinessId::class,
    ];

    public function __construct(public int $columnIndex)
    {
    }

    public function transform(Data $data, TransformContext $transformContext): void
    {
    }

    protected function getOptionsResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired(['index', 'type'])
            ->setAllowedValues('type', \array_keys(self::TYPES))
        ;

        return $optionsResolver;
    }
}
