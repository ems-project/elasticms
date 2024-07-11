<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\User;

use EMS\CoreBundle\Controller\CoreControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    use CoreControllerTrait;

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EMSCore/user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function logout(): never
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
