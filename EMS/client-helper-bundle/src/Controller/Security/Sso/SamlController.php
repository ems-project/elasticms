<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\Security\Sso;

use EMS\ClientHelperBundle\Security\Sso\Saml\SamlService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class SamlController
{
    public function __construct(
        private readonly SamlService $samlService,
    ) {
    }

    public function metaData(): Response
    {
        $metaData = $this->samlService->auth()->getSettings()->getSPMetadata();

        $response = new Response($metaData);
        $response->headers->set('Content-Type', 'xml');

        return $response;
    }

    public function login(Request $request): void
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
            if (\is_object($error) && \method_exists($error, 'getMessage')) {
                $error = $error->getMessage();
            }
            throw new \RuntimeException($error);
        }

        $targetPath = $session->get('_security.main.target_path');

        $this->samlService->auth()->login($targetPath);
    }
}
