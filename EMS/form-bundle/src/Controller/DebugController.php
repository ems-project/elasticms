<?php

declare(strict_types=1);

namespace EMS\FormBundle\Controller;

use EMS\FormBundle\Components\Form;
use EMS\FormBundle\Submission\Client;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class DebugController extends AbstractFormController
{
    /**
     * @param string[] $locales
     */
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly Client $client,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
        private readonly array $locales,
    ) {
    }

    public function iframe(Request $request, string $ouuid): Response
    {
        $form = $this->formFactory->create(Form::class, [], [
            'ouuid' => $ouuid,
            'locale' => $request->getLocale(),
            'use_cache' => false,
        ]);

        return new Response($this->twig->render('@EMSForm/debug/iframe.html.twig', [
            'config' => $this->getFormConfig($form, $request),
            'locales' => $this->locales,
            'url' => $request->getSchemeAndHttpHost().$request->getBasePath(),
        ]));
    }

    public function form(Request $request, string $ouuid): Response
    {
        $formOptions = [
            'ouuid' => $ouuid,
            'locale' => $request->getLocale(),
            'use_cache' => false,
        ];

        if (!$request->query->getBoolean('validate', true)) {
            $formOptions['attr'] = ['novalidate' => 'novalidate'];
        }

        $form = $this->formFactory->create(Form::class, [], $formOptions);
        $form->handleRequest($request);

        $responses = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $responses = $this->client->submit($form, $ouuid);
        }

        return new Response($this->twig->render('@EMSForm/debug/form.html.twig', [
            'form' => $form->createView(),
            'locales' => $this->locales,
            'responses' => $responses,
            'url' => $request->getSchemeAndHttpHost().$request->getBasePath(),
        ]));
    }

    public function dynamicFieldAjax(Request $request, string $ouuid): Response
    {
        $forward = $this->router->generate('emsf_dynamic_field_ajax', ['ouuid' => $ouuid, '_locale' => $request->getLocale()]);

        return new RedirectResponse($forward, Response::HTTP_TEMPORARY_REDIRECT);
    }
}
