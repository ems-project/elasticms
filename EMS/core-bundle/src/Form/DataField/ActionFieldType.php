<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\DataField;

use Symfony\Component\Form\FormBuilderInterface;

class ActionFieldType extends DataFieldType
{
    #[\Override]
    public function buildOptionsForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildOptionsForm($builder, $options);
        $optionsForm = $builder->get('options');

        $optionsForm
            ->remove('mappingOptions')
            ->remove('migrationOptions')
            ->remove('extraOptions');

        $restrictionOptions = $optionsForm->get('restrictionOptions');
        $restrictionOptions->remove('mandatory')->remove('mandatory_if');
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'action';
    }

    #[\Override]
    public static function getIcon(): string
    {
        return 'fa fa-cog';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'Action field';
    }
}
