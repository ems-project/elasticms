<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Revision;

use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\DataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class TrashController extends AbstractController
{
    public function __construct(
        private readonly DataService $dataService,
        private readonly string $templateNamespace
    ) {
    }

    public function trash(ContentType $contentType): Response
    {
        if (!$this->isGranted($contentType->role(ContentTypeRoles::TRASH))) {
            throw $this->createAccessDeniedException('Trash not granted!');
        }

        return $this->render("@$this->templateNamespace/data/trash.html.twig", [
            'contentType' => $contentType,
            'revisions' => $this->dataService->getAllDeleted($contentType),
        ]);
    }

    public function putBack(ContentType $contentType, string $ouuid): RedirectResponse
    {
        $revId = $this->dataService->putBack($contentType, $ouuid);

        return $this->redirectToRoute(Routes::EDIT_REVISION, [
            'revisionId' => $revId,
        ]);
    }

    public function emptyTrash(ContentType $contentType, string $ouuid): RedirectResponse
    {
        $this->dataService->emptyTrash($contentType, $ouuid);

        return $this->redirectToRoute(Routes::DATA_TRASH, [
            'contentType' => $contentType->getId(),
        ]);
    }
}
