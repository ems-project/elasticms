<?php

declare(strict_types=1);

namespace EMS\FormBundle\Form\Extension;

use EMS\FormBundle\Components\Constraint\IsVerificationCode;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfirmationExtension extends AbstractTypeExtension
{
    /**
     * @param FormInterface<FormInterface> $form
     * @param array<string, mixed>         $options
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        if (null === $isVerificationCode = $this->getVerificationCodeConstraint($options)) {
            return;
        }

        $view->vars['confirmation_value_field'] = $isVerificationCode->field;
    }

    /** @param array<string, mixed> $options */
    private function getVerificationCodeConstraint(array $options): ?IsVerificationCode
    {
        foreach ($options['constraints'] as $constraint) {
            if ($constraint instanceof IsVerificationCode) {
                return $constraint;
            }
        }

        return null;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(['confirmation_value_field' => null]);
    }

    /** @return string[] */
    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [NumberType::class, HiddenType::class];
    }
}
