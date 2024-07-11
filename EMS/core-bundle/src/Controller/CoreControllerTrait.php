<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param array<string, mixed> $parameters
     */
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $templateNamespace = parent::getParameter('ems_core.template_namespace');

        if (\is_string($templateNamespace) && 'EMSCore' !== $templateNamespace) {
            $view = u($view)->replaceMatches('/^@EMSCore/', '@'.$templateNamespace)->toString();
        }

        return parent::render($view, $parameters, $response);
    }
}
