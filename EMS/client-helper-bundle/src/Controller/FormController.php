<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Form\EmschFormType;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class FormController
{
    public const string BLOCK_SUCCESS_REDIRECT = 'emschFormSuccessRedirect';
    public const string BLOCK_DATA = 'emschFormData';

    public function __construct(
        private Handler $handler,
        private FormFactoryInterface $formFactory,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $template = $this->handler->handle($request);

        $data = $template->jsonBlock(self::BLOCK_DATA);

        $form = $this->formFactory->create(EmschFormType::class, $data, ['template' => $template]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $template->context()->append(['emschFormData' => $form->getData()]);

            if ($redirect = $template->renderBlock(self::BLOCK_SUCCESS_REDIRECT)) {
                return new RedirectResponse($redirect);
            }
        }

        $template->context()->append(['emschForm' => $form->createView()]);

        return new Response($template->render());
    }
}
