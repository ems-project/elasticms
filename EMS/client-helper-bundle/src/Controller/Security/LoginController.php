<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\Security;

use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\ClientHelperBundle\Security\Login\LoginCredentials;
use EMS\ClientHelperBundle\Security\Login\LoginForm;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

readonly class LoginController
{
    public function __construct(
        private Handler $handler,
        private FormFactory $formFactory,
    ) {
    }

    public function __invoke(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $template = $this->handler->handle($request);

        $credentials = new LoginCredentials();
        $credentials->username = $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME);

        $template->context()->append([
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $this->formFactory->create(LoginForm::class, $credentials)->createView(),
        ]);

        return new Response($template->render());
    }
}
