<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Wysiwyg;

use EMS\CoreBundle\Service\WysiwygProfileService;
use EMS\Helpers\Standard\Html;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AjaxPasteController
{
    public function __construct(private readonly WysiwygProfileService $wysiwygProfileService)
    {
    }

    public function __invoke(Request $request, int $wysiwygProfileId): JsonResponse
    {
        if (null === $profile = $this->wysiwygProfileService->getById($wysiwygProfileId)) {
            throw new NotFoundHttpException('Wysiwyg profile not found');
        }

        $config = $profile->getConfig() ? Json::decode($profile->getConfig()) : [];
        $pasteConfig = $config['ems']['paste'] ?? [];

        $content = Json::decode($request->getContent())['content'] ?? '';

        $html = (new Html($content))
            ->sanitize($pasteConfig['sanitize'] ?? [])
            ->prettyPrint($pasteConfig['prettyPrint'] ?? []);

        return new JsonResponse(['content' => (string) $html]);
    }
}
