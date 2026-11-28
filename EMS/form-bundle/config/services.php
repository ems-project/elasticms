<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestManagerInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\FormBundle\Components\Constraint\IsExpressionValidator;
use EMS\FormBundle\Components\Constraint\IsRequiredIfValidator;
use EMS\FormBundle\Components\Constraint\IsVerificationCodeValidator;
use EMS\FormBundle\Components\Form;
use EMS\FormBundle\Components\Form\NestedChoiceType;
use EMS\FormBundle\Components\Form\SubFormType;
use EMS\FormBundle\Contracts\Confirmation\VerificationCodeGeneratorInterface;
use EMS\FormBundle\Contracts\EndpointManagerInterface;
use EMS\FormBundle\Controller\ConfirmationController;
use EMS\FormBundle\Controller\DebugController;
use EMS\FormBundle\Controller\FormController;
use EMS\FormBundle\Form\Extension\ConfirmationExtension;
use EMS\FormBundle\Form\Extension\FieldExtension;
use EMS\FormBundle\FormConfig\FormConfigFactory;
use EMS\FormBundle\Security\Guard;
use EMS\FormBundle\Service\Confirmation\ConfirmationService;
use EMS\FormBundle\Service\Confirmation\Endpoint\HttpEndpointType;
use EMS\FormBundle\Service\Confirmation\VerificationCodeGenerator;
use EMS\FormBundle\Service\Endpoint\EndpointManager;
use EMS\FormBundle\Submission\Client;
use EMS\FormBundle\Twig\FormExtension;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('emsf.form_config.factory', FormConfigFactory::class)
        ->args([
            service(ClientRequestManagerInterface::class),
            service('cache.app'),
            service('logger'),
            service('ems.twig_extension.text'),
            '%emsf.ems_config%',
        ])
        ->tag('monolog.logger', ['channel' => 'ems_common']);

    $services->set('emsf.form', Form::class)
        ->args([service('emsf.form_config.factory')])
        ->tag('form.type');

    $services->set('emsf.subform', SubFormType::class)
        ->args([service('emsf.form_config.factory')])
        ->tag('form.type');

    $services->set('emsf.nested.choice', NestedChoiceType::class)
        ->args([service('emsf.form_config.factory')])
        ->tag('form.type');

    $services->set('emsf.submission.client', Client::class)
        ->args([
            service(ClientRequestManagerInterface::class),
            tagged_iterator('emsf.handler'),
        ]);

    $services->set('emsf.security.guard', Guard::class)
        ->args([
            service('logger'),
            '%emsf.hashcash.difficulty%',
        ]);

    $services->set('emsf.service.confirmation', ConfirmationService::class)
        ->args([
            service('emsf.form_config.factory'),
            service('security.csrf.token_manager'),
            service('logger'),
            service('emsf.endpoint_manager'),
        ]);

    $services->alias(VerificationCodeGeneratorInterface::class, 'emsf.service.confirmation.verification_code_generator');

    $services->set('emsf.service.confirmation.verification_code_generator', VerificationCodeGenerator::class)
        ->args([
            service(CoreApiInterface::class),
            service('request_stack'),
        ]);

    $services->alias(EndpointManagerInterface::class, 'emsf.endpoint_manager');

    $services->set('emsf.endpoint_manager', EndpointManager::class)
        ->args([
            '%emsf.endpoints%',
            tagged_iterator('emsf.endpoint.type'),
            service('logger'),
        ]);

    $services->set('emsf.endpoint.confirmation.http', HttpEndpointType::class)
        ->args([
            service(HttpClientInterface::class),
            service('translator'),
            service(VerificationCodeGeneratorInterface::class),
        ])
        ->tag('emsf.endpoint.type');

    $services->set('emsf.validator.verification_code', IsVerificationCodeValidator::class)
        ->args([service('emsf.service.confirmation')])
        ->tag('validator.constraint_validator', ['alias' => IsVerificationCodeValidator::class]);

    $services->set('emsf.validator.required_if', IsRequiredIfValidator::class)
        ->args([service('logger')])
        ->tag('validator.constraint_validator', ['alias' => IsRequiredIfValidator::class]);

    $services->set('emsf.validator.expression', IsExpressionValidator::class)
        ->args([service(ExpressionServiceInterface::class)])
        ->tag('validator.constraint_validator', ['alias' => IsExpressionValidator::class]);

    $services->set('emsf.form.extension.field', FieldExtension::class)
        ->tag('form.type_extension', ['priority' => 1]);

    $services->set('emsf.form.extension.confirmation', ConfirmationExtension::class)
        ->tag('form.type_extension', ['priority' => 0]);

    $services->set(DebugController::class)
        ->public()
        ->args([
            service('form.factory'),
            service('emsf.submission.client'),
            service('twig'),
            service('router'),
            '%emsch.locales%',
        ]);

    $services->set(FormController::class)
        ->public()
        ->args([
            service('form.factory'),
            service('emsf.submission.client'),
            service('emsf.security.guard'),
            service('twig'),
            service('security.csrf.token_manager'),
        ]);

    $services->set(ConfirmationController::class)
        ->public()
        ->args([
            service('emsf.security.guard'),
            service('emsf.service.confirmation'),
            service('logger'),
        ]);

    $services->set('emsf.twig_extension.form', FormExtension::class)
        ->args([
            service('logger'),
            service('emsf.endpoint_manager'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');
};
