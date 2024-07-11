<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

use function Symfony\Component\String\u;

trait CoreControllerTrait
{
    protected function getClickedButtonName(FormInterface $form): ?string
    {
        if (!$form instanceof Form) {
            return null;
        }

        $clickedButton = $form->getClickedButton();

        return $clickedButton instanceof FormInterface ? $clickedButton->getName() : null;
    }

    protected function getTemplateNamespace(): string
    {
        $templateNamespace = parent::getParameter('ems_core.template_namespace');

        return \is_string($templateNamespace) ? $templateNamespace : 'EMSCore';
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function renderView(string $view, array $parameters = []): string
    {
        $templateNamespace = $this->getTemplateNamespace();

        if ('EMSCore' !== $templateNamespace) {
            $view = u($view)->replaceMatches('/^@EMSCore/', '@'.$templateNamespace)->toString();
        }

        return parent::renderView($view, $parameters);
    }
}
