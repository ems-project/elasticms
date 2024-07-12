<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Twig\Environment;

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
        /** @var Environment $twig */
        $twig = $this->container->get('twig');
        $templateNamespace = $this->getTemplateNamespace();

        if ('EMSCore' !== $templateNamespace) {
            $namespaceView = u($view)->replaceMatches('/^@EMSCore/', '@'.$templateNamespace)->toString();

            if ($twig->getLoader()->exists($namespaceView)) {
                return $twig->render($namespaceView, $parameters);
            }
        }

        return $twig->render($view, $parameters);
    }
}
