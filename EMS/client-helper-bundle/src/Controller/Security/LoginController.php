<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\Security;

use EMS\ClientHelperBundle\Helper\Request\Handler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twig\Environment;
use Twig\Error\RuntimeError;

class LoginController
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Environment $templating
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $result = $this->handler->handle($request);

        try {
            $response = new Response($this->templating->render($result['template'], $result['context']));
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }

        return $response;
    }
}
