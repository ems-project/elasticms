<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use EMS\CoreBundle\Core\InlineEditor\Dto\ElementDto;
use EMS\CoreBundle\Core\InlineEditor\InlineEditor;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class InlineEditorController
{
    public function __construct(
        private InlineEditor $inlineEditor
    ) {
    }

    public function editor(string $channel, ?string $path): Response
    {
        return new Response($this->inlineEditor->renderEditor($channel, $path));
    }

    public function apiInit(Request $request): JsonResponse
    {
        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        $data = Json::decode($request->getContent());
        $elements = \array_map(fn (array $element) => ElementDto::fromArray($element), $data['elements'] ?? []);

        return new JsonResponse($this->inlineEditor->apiInit($elements));
    }

    public function apiEdit(Request $request): JsonResponse
    {
        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        $data = Json::decode($request->getContent());

        return new JsonResponse($this->inlineEditor->apiEdit(ElementDto::fromArray($data['element'])));
    }

    public function apiDiscard(int $draftId): JsonResponse
    {
        return new JsonResponse([
            'success' => $this->inlineEditor->apiDiscard($draftId),
        ]);
    }

    public function apiAutoSave(Request $request): JsonResponse
    {
        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        $data = Json::decode($request->getContent());

        return new JsonResponse([
            'success' => $this->inlineEditor->apiAutoSave(
                draftId: $data['draftId'],
                element: ElementDto::fromArray($data['element']),
                content: $data['content'],
            ),
        ]);
    }
}
