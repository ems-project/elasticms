<?php

namespace EMS\Helpers\Translations;

use EMS\Helpers\Standard\Json;
use Google\Protobuf\Internal\EnumDescriptorProto\EnumReservedRange;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Translation
{
    public function __construct(private readonly string $label, private readonly ?Gender $gender = null, private readonly ?Number $number = null, private readonly ?bool $elision = null)
    {
    }

    /**
     * @param mixed[] $options
     * @return self
     */
    public static function fromArray(array $options): self
    {

        $optionResolver = new OptionsResolver();
        $optionResolver
            ->setDefaults([
                'gender' => null,
                'number' => null,
                'elision' => null,
            ])
            ->setRequired(['label', 'string'])
            ->setAllowedTypes('gender', ['null', 'string'])
            ->setAllowedTypes('number', ['null', 'string'])
            ->setNormalizer('gender', function (Options $options, $value) {
                return Gender::from($value);
            })
            ->setNormalizer('number', function (Options $options, $value) {
                return Number::from($value);
            });
        /** @var array{label: string, number: ?Number, gender: ?Gender, elision: ?bool} $resolvedOptions */
        $resolvedOptions = $optionResolver->resolve($options);
        
        return new self($resolvedOptions['label'], $resolvedOptions['gender'], $resolvedOptions['number'], $resolvedOptions['elision']);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function getNumber(): ?Number
    {
        return $this->number;
    }

    public function isElision(): ?bool
    {
        return $this->elision;
    }
    
    
}