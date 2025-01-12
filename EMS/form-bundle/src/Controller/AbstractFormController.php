<?php

namespace EMS\FormBundle\Controller;

use EMS\FormBundle\FormConfig\FormConfig;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFormController
{
    /**
     * @param FormInterface<mixed> $form
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
}
