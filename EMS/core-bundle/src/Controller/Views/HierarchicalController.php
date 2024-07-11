<?php

namespace EMS\CoreBundle\Controller\Views;

use EMS\CommonBundle\Elasticsearch\Exception\NotFoundException;
use EMS\CoreBundle\Controller\CoreControllerTrait;
use EMS\CoreBundle\Entity\View;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HierarchicalController extends AbstractController
{
    use CoreControllerTrait;

    public function __construct(
        private readonly ContentTypeService $contentTypeService,
        private readonly SearchService $searchService
    ) {
    }

    public function item(View $view, string $key): Response
    {
        $ouuid = \explode(':', $key);
        $contentType = $this->contentTypeService->getByName($ouuid[0]);
        if (false === $contentType) {
            throw $this->createNotFoundException(\sprintf('Content type %s not found', $ouuid[0]));
        }
        try {
            $document = $this->searchService->getDocument($contentType, $ouuid[1]);
        } catch (NotFoundException) {
            throw $this->createNotFoundException(\sprintf('Document %s not found', $ouuid[1]));
        }

        return $this->render('@EMSCore/view/custom/hierarchical_add_item.html.twig', [
            'data' => $document->getSource(),
            'view' => $view,
            'contentType' => $contentType,
            'key' => $ouuid,
            'child' => $key,
        ]);
    }
}
