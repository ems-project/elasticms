<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\Security;

use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\ClientHelperBundle\Security\Login\LoginCredentials;
use EMS\ClientHelperBundle\Security\Login\LoginForm;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Twig\Environment;
use Twig\Error\RuntimeError;

class LoginController
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Environment $templating,
        private readonly FormFactory $formFactory,
    ) {
    }

    public function __invoke(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $result = $this->handler->handle($request);

        $credentials = new LoginCredentials();
        $credentials->username = $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME);

        $context = [...$result['context'], ...[
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $this->formFactory->create(LoginForm::class, $credentials)->createView(),
        ]];

        try {
            $response = new Response($this->templating->render($result['template'], $context));
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }

        return $response;
    }
}
