<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\InlineEdit\Dto\PayloadDto;
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
        private SerializerInterface $serializer,
    ) {
    }

    public function editor(EmschRequest $request, ?string $path): Response
    {
        if (!$request->isInlineEditorEnabled()) {
            throw new NotFoundHttpException();
        }

        return new Response($this->inlineEditHelper->renderEditor($request, $path));
    }

    public function apiRender(EmschRequest $request): JsonResponse
    {
        return new JsonResponse($this->inlineEditHelper->render(
            payload: $this->getPayload($request)
        ));
    }

    public function apiDraft(EmschRequest $request): JsonResponse
    {
        return new JsonResponse($this->inlineEditHelper->createDraft(
            payload: $this->getPayload($request)
        ));
    }

    private function getPayload(EmschRequest $request): PayloadDto
    {
        if (!$request->isInlineEditorEnabled()) {
            throw new NotFoundHttpException();
        }

        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        return $this->serializer->deserialize($request->getContent(), PayloadDto::class, 'json');
    }
}
