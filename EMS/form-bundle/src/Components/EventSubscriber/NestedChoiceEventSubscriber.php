<?php

namespace EMS\FormBundle\Components\EventSubscriber;

use EMS\FormBundle\Components\Field\FieldInterface;
use EMS\FormBundle\FormConfig\FieldChoicesConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class NestedChoiceEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly FieldInterface $field, private readonly FieldChoicesConfig $choices)
    {
    }

    /** @return string[] */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $fieldName = $this->initialFieldName($form);
        $data = $event->getData();
        if (!\is_array($data)) {
            $data = [];
        }

        $parentChoice = $data[$fieldName] ?? null;
        for ($level = 1; $level <= $this->choices->getMaxLevel(); ++$level) {
            $fieldName = $this->nextFieldName($fieldName);
            if (\is_string($parentChoice)) {
                $this->addField($fieldName, $parentChoice, $form, $data[$fieldName] ?? null);
                $parentChoice = $data[$fieldName] ?? null;
            } else {
                $form->add($fieldName, HiddenType::class);
            }
        }
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data || !\is_array($data)) {
            return;
        }

        foreach ($data as $fieldName => $choice) {
            if ('' === $choice) {
                continue;
            }

            $this->addField($this->nextFieldName($fieldName), $choice, $form);
        }
    }

    private function nextFieldName(string $name): string
    {
        $split = \explode('_', $name);

        return \sprintf('level_%d', (int) $split[1] + 1);
    }

    /**
     * @param FormInterface<mixed> $form
     */
    private function initialFieldName(FormInterface $form): string
    {
        $fields = $form->all();
        $firstField = \reset($fields);

        if (false === $firstField) {
            return '';
        }

        return $firstField->getName();
    }

    /**
     * @param FormInterface<mixed> $form
     */
    private function addField(string $fieldName, string $choice, FormInterface $form, ?string $defaultData = null): void
    {
        $options = $this->field->getOptions();

        try {
            $this->choices->addChoice($choice);
        } catch (\Exception) {
            return;
        }

        if (0 === \count($this->choices->list())) {
            return;
        }

        $options['choices'] = $this->choices->list();
        $options['label'] = $this->choices->listLabel();
        $options['data'] = $defaultData;
        $form->add($fieldName, $this->field->getFieldClass(), $options);
        $form->get($fieldName)->setData($choice);
    }
}
