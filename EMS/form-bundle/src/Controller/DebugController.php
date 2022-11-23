<?php

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
    private FormFactory $formFactory;
    private Client $client;
    private Environment $twig;
    /** @var string[] */
    private array $locales = [];
    private RouterInterface $router;

    /**
     * @param string [] $locales
     */
    public function __construct(FormFactory $formFactory, Client $client, Environment $twig, RouterInterface $router, array $locales)
    {
        $this->formFactory = $formFactory;
        $this->client = $client;
        $this->twig = $twig;
        $this->locales = $locales;
        $this->router = $router;
    }

    public function iframe(Request $request, string $ouuid): Response
    {
        $form = $this->formFactory->create(Form::class, [], $this->getFormOptions($ouuid, $request->getLocale()));

        return new Response($this->twig->render('@EMSForm/debug/iframe.html.twig', [
            'config' => $this->getFormConfig($form, $request),
            'locales' => $this->locales,
            'url' => $request->getSchemeAndHttpHost().$request->getBasePath(),
        ]));
    }

    public function form(Request $request, string $ouuid): Response
    {
        $formOptions = $this->getFormOptions($ouuid, $request->getLocale());

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
            'response' => $responses,
            'url' => $request->getSchemeAndHttpHost().$request->getBasePath(),
        ]));
    }

    public function dynamicFieldAjax(Request $request, string $ouuid): Response
    {
        $forward = $this->router->generate('_emsf_dynamic_field_ajax', ['ouuid' => $ouuid, '_locale' => $request->getLocale()]);

        return new RedirectResponse($forward, 307);
    }
}
