<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\InlineEdit\InlineEditHelper;
use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class InlineEditController
{
    public function __construct(
        private InlineEditHelper $inlineEditHelper,
    ) {
    }

    public function editor(EmschRequest $request, string $path): Response
    {
        if (!$request->isInlineEditorEnabled()) {
            throw new NotFoundHttpException();
        }

        return new Response($this->inlineEditHelper->renderEditor($request, $path));
    }
}
