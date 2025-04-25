<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Action;

use EMS\CoreBundle\Core\ContentType\FieldType\FieldTypeTreeItem;
use EMS\CoreBundle\Entity\FieldType;
use EMS\Helpers\Standard\Json;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionRevisionConfig
{
    /**
     * @param FieldType[] $input
     * @param FieldType[] $output
     */
    public function __construct(
        public array $input = [],
        public array $output = [],
    ) {
    }

    /**
     * @return string[]
     */
    public function outputNames(): array
    {
        return \array_map(static fn (FieldType $field) => 'revision[data]'.$field->getPath(), $this->output);
    }

    public static function fromFieldType(FieldType $fieldType, FieldTypeTreeItem $fieldTree): ActionRevisionConfig
    {
        $jsonConfig = $fieldType->getExtraOption('config', '{}');
        $config = self::getConfigResolver($fieldTree)->resolve(Json::decode($jsonConfig));

        return new self($config['input'], $config['output']);
    }

    private static function getConfigResolver(FieldTypeTreeItem $fieldTree): OptionsResolver
    {
        $fieldNormalizer = function (Options $options, array $value) use ($fieldTree) {
            return \array_filter(\array_map(
                callback: static fn (string $name) => $fieldTree->findByName($name)?->getFieldType(),
                array: $value
            ));
        };

        $optionResolver = new OptionsResolver();
        $optionResolver
            ->setIgnoreUndefined()
            ->setRequired(['input', 'output'])
            ->setAllowedTypes('input', ['array'])
            ->setAllowedTypes('output', ['array'])
            ->setNormalizer('input', $fieldNormalizer)
            ->setNormalizer('output', $fieldNormalizer)
        ;

        return $optionResolver;
    }
}
