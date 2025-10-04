<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class EmschChoiceDynamicType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_loader' => new class implements ChoiceLoaderInterface {
                /** @var string[] */
                private array $values = [];

                public function loadChoiceList(?callable $value = null): ChoiceListInterface
                {
                    return new ArrayChoiceList($this->values, $value);
                }

                public function loadChoicesForValues(array $values, ?callable $value = null): array
                {
                    $this->values = $values;

                    return $values;
                }

                public function loadValuesForChoices(array $choices, ?callable $value = null): array
                {
                    $this->values = \array_combine($choices, $choices);

                    return $this->values;
                }
            },
        ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
