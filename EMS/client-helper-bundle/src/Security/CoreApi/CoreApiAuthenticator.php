<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\CoreApi;

use EMS\ClientHelperBundle\Security\CoreApi\User\CoreApiUserProvider;
use EMS\ClientHelperBundle\Security\Login\LoginCredentials;
use EMS\ClientHelperBundle\Security\Login\LoginForm;
use EMS\CommonBundle\Contracts\CoreApi\Exception\NotAuthenticatedExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class CoreApiAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    final public const string CSRF_ID = 'login';

    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly CoreApiFactory $coreApiFactory,
        private readonly CoreApiUserProvider $coreApiUserProvider,
        private readonly FormFactory $formFactory,
        private readonly LoggerInterface $logger,
        private readonly string $routeLogin,
    ) {
    }

    #[\Override]
    public function supports(Request $request): ?bool
    {
        return $request->isMethod(Request::METHOD_POST) && $request->get('_route') === $this->routeLogin;
    }

    #[\Override]
    public function authenticate(Request $request): Passport
    {
        $csrfToken = $request->request->get('token');

        if (!\is_string($csrfToken)) {
            throw new AuthenticationException('CSRF token missing');
        }

        $credentials = new LoginCredentials();
        $form = $this->formFactory->create(LoginForm::class, $credentials);
        $form->handleRequest($request);

        $request->getSession()->set(Security::LAST_USERNAME, $credentials->giveUsername());

        try {
            $coreApi = $this->coreApiFactory->create();
            $coreApi->authenticate($credentials->giveUsername(), $credentials->givePassword());
        } catch (\Throwable $e) {
            $key = $e instanceof NotAuthenticatedExceptionInterface ? 'emsch.security.exception.bad_credentials' : 'emsch.security.exception.error';
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString(), 'code' => $e->getCode()]);
            throw new AuthenticationException($key, 0, $e);
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $coreApi->getToken(),
                fn (string $token) => $this->coreApiUserProvider->loadUserByIdentifier($token)
            ),
            [new CsrfTokenBadge(self::CSRF_ID, $csrfToken)]
        );
    }

    #[\Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        return $this->httpUtils->createRedirectResponse($request, $targetPath ?? '/');
    }

    #[\Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, $this->routeLogin);
    }
}
