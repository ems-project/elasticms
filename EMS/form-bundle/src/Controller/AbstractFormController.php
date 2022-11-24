<?php

namespace EMS\FormBundle\Controller;

use EMS\FormBundle\FormConfig\FormConfig;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFormController
{
    /**
     * @param FormInterface<FormInterface> $form
     */
    protected function getFormConfig(FormInterface $form, Request $request): FormConfig
    {
        $config = $form->getConfig()->getOption('config');
        if (!$config instanceof FormConfig) {
            throw new \RuntimeException('invalid form config');
        }
        if (empty($config->getDomains())) {
            $config->addDomain($request->getSchemeAndHttpHost());
        }

        return $config;
    }

    /** @return string[] */
    protected function getFormOptions(string $ouuid, string $locale): array
    {
        return ['ouuid' => $ouuid, 'locale' => $locale];
    }

    /** @return mixed[] */
    protected function getDisabledValidationsFormOptions(string $ouuid, string $locale): array
    {
        return \array_merge($this->getFormOptions($ouuid, $locale), ['validation_groups' => false]);
    }
}
