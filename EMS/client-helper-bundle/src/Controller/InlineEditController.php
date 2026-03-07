<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\InlineEdit\Dto\RenderPayloadDto;
use EMS\ClientHelperBundle\Helper\InlineEdit\InlineEditHelper;
use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

readonly class InlineEditController
{
    public function __construct(
        private InlineEditHelper $inlineEditHelper,
    ) {
    }

    public function editor(EmschRequest $request, ?string $path): Response
    {
        if (!$request->isInlineEditorEnabled()) {
            throw new NotFoundHttpException();
        }

        return new Response($this->inlineEditHelper->renderEditor($request, $path));
    }

    public function apiRender(EmschRequest $request, SerializerInterface $serializer): JsonResponse
    {
        if (!$request->isInlineEditorEnabled()) {
            throw new NotFoundHttpException();
        }

        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        $jsonData = $request->getContent();
        $payload = $serializer->deserialize($jsonData, RenderPayloadDto::class, 'json');

        return new JsonResponse($this->inlineEditHelper->render($payload));
    }

    public function apiDraft(EmschRequest $request): JsonResponse
    {
        if (!$request->isInlineEditorEnabled()) {
            throw new NotFoundHttpException();
        }

        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        return new JsonResponse(['success' => true]);
    }
}
