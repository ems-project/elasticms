<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Controller\Security\LoginController;
use EMS\ClientHelperBundle\Controller\Security\Sso\OAuth2Controller;
use EMS\ClientHelperBundle\Controller\Security\Sso\SamlController;
use EMS\ClientHelperBundle\Security\CoreApi\CoreApiAuthenticator;
use EMS\ClientHelperBundle\Security\CoreApi\User\CoreApiUserProvider;
use EMS\ClientHelperBundle\Security\FirewallEntryPoint;
use EMS\ClientHelperBundle\Security\Login\LoginForm;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Authenticator;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use EMS\ClientHelperBundle\Security\Sso\Saml\SamlAuthenticator;
use EMS\ClientHelperBundle\Security\Sso\Saml\SamlService;
use EMS\ClientHelperBundle\Security\Sso\SsoService;
use EMS\ClientHelperBundle\Security\Sso\User\SsoUserProvider;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('emsch.controller.security.login', LoginController::class)
        ->args([
            service('emsch.routing.handler'),
            service('form.factory'),
        ])
        ->tag('controller.service_arguments');

    $services->set('emsch.security.firewall.entry_point', FirewallEntryPoint::class)
        ->args([
            service('security.http_utils'),
            service('router'),
            service('emsch.security.sso'),
            '%emsch.security.route_login%',
        ]);

    $services->set('emsch.security.form.login', LoginForm::class)
        ->args([service('emsch.manager.client_request')])
        ->tag('form.type');

    $services->set('emsch.security.core_api.user_provider', CoreApiUserProvider::class)
        ->args([
            service('ems_common.core_api'),
            service('logger'),
        ]);

    $services->set('emsch.security.core_api.authenticator', CoreApiAuthenticator::class)
        ->args([
            service('security.http_utils'),
            service('ems_common.core_api'),
            service('emsch.security.core_api.user_provider'),
            service('form.factory'),
            service('logger'),
            '%emsch.security.route_login%',
        ]);

    $services->set('emsch.security.sso', SsoService::class)
        ->args([
            service('emsch.security.sso.oauth2'),
            service('emsch.security.sso.saml'),
            service('emsch.security.sso.user_provider'),
            service('emsch.security.core_api.user_provider'),
            service('ems_common.core_api'),
            '%emsch.security.sso.core_user%',
        ]);

    $services->set('emsch.security.sso.user_provider', SsoUserProvider::class);

    $services->set('emsch.security.sso.oauth2', OAuth2Service::class)
        ->args([
            service('security.http_utils'),
            service('logger'),
            '%emsch.security.sso.oauth2%',
        ]);

    $services->set(OAuth2Controller::class)
        ->args([service('emsch.security.sso.oauth2')])
        ->tag('controller.service_arguments');

    $services->set('emsch.security.sso.oauth2.authenticator', OAuth2Authenticator::class)
        ->args([
            service('security.http_utils'),
            service('emsch.security.sso'),
        ]);

    $services->set('emsch.security.sso.saml', SamlService::class)
        ->args([
            service('request_stack'),
            service('security.http_utils'),
            '%emsch.security.sso.saml%',
        ]);

    $services->set(SamlController::class)
        ->args([service('emsch.security.sso.saml')])
        ->tag('controller.service_arguments');

    $services->set('emsch.security.sso.saml.authenticator', SamlAuthenticator::class)
        ->args([
            service('security.http_utils'),
            service('emsch.security.sso'),
        ]);
};
