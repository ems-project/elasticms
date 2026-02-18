<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class InlineEditController
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function editor(Request $request): Response
    {
        return new Response($this->twig->render('@EMSClientHelper/inlineEdit/editor.html.twig', [
            'iframeSrc' => \preg_replace('#/editor/#', '/', $request->getPathInfo(), 1),
        ]));
    }
}
