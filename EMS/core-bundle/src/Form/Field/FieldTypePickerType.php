<?php

namespace EMS\CoreBundle\Form\Field;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Data\Data;
use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Form\DataField\DataFieldType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldTypePickerType extends Select2Type
{
    /** @var array<string, DataFieldType> */
    private array $dataFieldTypes = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function addDataFieldType(DataFieldType $dataField): void
    {
        $this->dataFieldTypes[$dataField::class] = $dataField;
    }

    public function getDataFieldType(string $dataFieldTypeId): DataFieldType
    {
        return $this->dataFieldTypes[$dataFieldTypeId];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        dump($this->dataFieldTypes);
        $resolver->setDefaults([
            'choices' => \array_keys($this->dataFieldTypes),
            'attr' => [
                    'data-live-search' => true,
            ],
            'choice_attr' => function ($category, $key, $index) {
                $dataFieldType = $this->dataFieldTypes[$index];

                return [
                    'data-content' => '<div class="text-'.$category.'"><i class="'.$dataFieldType->getIcon().'"></i>&nbsp;&nbsp;'.$dataFieldType->getLabel().'</div>',
                ];
            },
            'choice_value' => fn ($value) => $value,
            'choice_label' => function ($choice, string $key, mixed $value): string {
                /* @var ?DataFieldType $choice */
                $choice = $this->dataFieldTypes[$value] ?? null;

                return $choice?->getLabel();

                // or if you want to translate some key
                //return 'form.choice.'.$key;
                //return new TranslatableMessage($key, false === $choice ? [] : ['%status%' => $value], 'store');
            },
        ]);
    }
}
