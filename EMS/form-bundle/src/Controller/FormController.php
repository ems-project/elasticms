<?php

declare(strict_types=1);

namespace EMS\FormBundle\Controller;

use EMS\FormBundle\Components\Form;
use EMS\FormBundle\Components\ValueObject\SymfonyFormFieldsByNameArray;
use EMS\FormBundle\Security\Guard;
use EMS\FormBundle\Submission\Client;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Twig\Environment;

class FormController extends AbstractFormController
{
    public function __construct(private readonly FormFactory $formFactory, private readonly Client $client, private readonly Guard $guard, private readonly Environment $twig, private readonly CsrfTokenManager $csrfTokenManager)
    {
    }

    public function iframe(Request $request, string $ouuid): Response
    {
        $form = $this->formFactory->create(Form::class, [], ['ouuid' => $ouuid, 'locale' => $request->getLocale()]);

        return new Response($this->twig->render('@EMSForm/iframe.html.twig', [
            'config' => $this->getFormConfig($form, $request),
        ]));
    }

    public function submitForm(Request $request, string $ouuid): JsonResponse
    {
        if (!$this->guard->checkForm($request)) {
            throw new AccessDeniedHttpException('access denied');
        }

        $form = $this->formFactory->create(Form::class, [], ['ouuid' => $ouuid, 'locale' => $request->getLocale()]);
        $form->handleRequest($request);
        $this->csrfTokenManager->removeToken('form');

        if ($form->isSubmitted() && $form->isValid()) {
            return new JsonResponse($this->client->submit($form, $ouuid));
        }

        return $this->generateFormResponse($ouuid, $form, $request);
    }

    public function initForm(Request $request, string $ouuid): JsonResponse
    {
        $content = $request->getContent();
        if (!\is_string($content)) {
            throw new \RuntimeException('Unexpected non-string request content');
        }
        if (Json::isEmpty($content)) {
            $data = [];
        } else {
            $data = Json::decode($content);
        }

        $form = $this->formFactory->create(Form::class, $data, ['ouuid' => $ouuid, 'locale' => $request->getLocale()]);

        return $this->generateFormResponse($ouuid, $form, $request);
    }

    public function dynamicFieldAjax(Request $request, string $ouuid): Response
    {
        $form = $this->formFactory->create(Form::class, [], [
            'ouuid' => $ouuid,
            'locale' => $request->getLocale(),
            'validation_groups' => false,
        ]);
        $form->handleRequest($request);

        $dynamicFields = new SymfonyFormFieldsByNameArray($request->request->all());
        $excludeFields = ['form__token'];

        return new JsonResponse([
            'ouuid' => $ouuid,
            'instruction' => 'dynamic',
            'response' => $this->twig->render('@EMSForm/nested_choice_form.html.twig', [
                'form' => $form->createView(),
            ]),
            'dynamicFields' => $dynamicFields->getFieldIdsJson($excludeFields),
        ]);
    }

    /**
     * @param FormInterface<mixed> $form
     */
    private function generateFormResponse(string $ouuid, FormInterface $form, Request $request): JsonResponse
    {
        $template = $this->getFormConfig($form, $request)->getTemplate();

        return new JsonResponse([
            'ouuid' => $ouuid,
            'instruction' => 'form',
            'response' => $this->twig->render($template, ['form' => $form->createView()]),
            'difficulty' => $this->guard->getDifficulty(),
        ]);
    }
}
