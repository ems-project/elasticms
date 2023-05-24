<?php

declare(strict_types=1);

namespace EMS\OneLoginBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OneLogin\Saml2\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

class SamlController extends AbstractController
{
    public function __construct(private readonly Auth $auth) {}

    public function metaData(): Response
    {
        $metadata = $this->auth->getSettings()->getSPMetadata();

        $response = new Response($metadata);
        $response->headers->set('Content-Type', 'xml');

        return $response;
    }

    public function acs(): Response
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall.');
    }

    public function login(Request $request): Response
    {
        $session = $request->getSession();
        $authErrorKey = Security::AUTHENTICATION_ERROR;

        $error = null;

        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif ($session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        }

        if ($error) {
            if (\is_object($error) && method_exists($error, 'getMessage')) {
                $error = $error->getMessage();
            }
            throw new \RuntimeException($error);
        }

        $targetPath = $session->get('_security.secure.target_path');

        $this->auth->login($targetPath);
    }

    public function logout(): Response
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}