<?php

namespace EMS\CoreBundle\Controller\Wysiwyg;

use EMS\CoreBundle\Core\UI\FlashMessageLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Environment;

class ModalController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FlashMessageLogger $flashMessageLogger,
        private readonly string $templateNamespace,
    ) {
    }

    public function loadLinkModal(): JsonResponse
    {
        return $this->flashMessageLogger->buildJsonResponse([
            'body' => $this->twig->render("@$this->templateNamespace/modal/link.html.twig"),
        ]);
    }
}
