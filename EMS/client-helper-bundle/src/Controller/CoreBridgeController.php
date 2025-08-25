<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class CoreBridgeController
{
    public function __construct(
        private CoreBridgeInterface $coreBridge,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function api(): void
    {
        // keep for generating the base url for calling the api.
    }

    public function getVersions(): JsonResponse
    {
        return new JsonResponse($this->coreBridge->versions());
    }

    public function autoSave(Request $request, string $contentType, int $revisionId): JsonResponse
    {
        return new JsonResponse([
            'success' => $this->coreBridge->data($contentType)->autoSave(
                revisionId: $revisionId,
                rawData: Json::decode(Type::string($request->getContent()))
            )->success(),
        ]);
    }

    public function fileInitUpload(Request $request): JsonResponse
    {
        $requestContent = $request->getContent();
        $json = Json::decode($requestContent);

        $hash = $json['hash'];

        $uploaded = $this->coreBridge->file()->initUpload(
            hash: $hash,
            size: $json['size'],
            filename: $json['name'],
            mimetype: $json['type']
        );

        return new JsonResponse([
            'uploaded' => $uploaded,
            'chunkUrl' => $this->urlGenerator->generate('emsch_api_file_chunk', ['hash' => $hash]),
        ]);
    }

    public function fileChunk(Request $request, string $hash): JsonResponse
    {
        $chunk = $request->getContent();

        if (!\is_string($chunk)) {
            throw new BadRequestHttpException();
        }

        return new JsonResponse([
            'uploaded' => $this->coreBridge->file()->addChunk($hash, $chunk),
        ]);
    }
}
