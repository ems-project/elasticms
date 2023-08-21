<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Component;

use EMS\CommonBundle\Json\JsonMenuNestedException;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfigException;
use EMS\CoreBundle\Core\Component\JsonMenuNested\JsonMenuNestedService;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Template\JsonMenuNestedTemplate;
use EMS\CoreBundle\Core\Revision\RawDataTransformer;
use EMS\CoreBundle\Core\UI\AjaxModalResponse;
use EMS\CoreBundle\Core\UI\AjaxService;
use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Form\Form\RevisionJsonMenuNestedType;
use EMS\CoreBundle\Service\DataService;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class JsonMenuNestedController
{
    public function __construct(
        private readonly JsonMenuNestedService $jsonMenuNestedService,
        private readonly AjaxService $ajaxService,
        private readonly DataService $dataService,
        private readonly FormFactory $formFactory,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function structure(Request $request, JsonMenuNestedConfig $config): JsonResponse
    {
        $data = Json::decode($request->getContent());
        $structure = $this->jsonMenuNestedService->getStructure($config, $data);

        return new JsonResponse(['structure' => $structure]);
    }

    public function itemAdd(Request $request, JsonMenuNestedConfig $config, string $parentId, int $nodeId): JsonResponse
    {
        try {
            $parent = $config->jsonMenuNested->giveItemById($parentId);
            $node = $config->nodes->getById($nodeId);
            $fieldType = $node->getFieldType();
            $contentType = $config->revision->giveContentType();

            $object = [];
            $data = RawDataTransformer::transform($node->getFieldType(), $object);

            $form = $this->formFactory->create(RevisionJsonMenuNestedType::class, ['data' => $data], [
                'field_type' => $fieldType,
                'content_type' => $contentType,
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                $formDataField = $form->get('data');
                $object = RawDataTransformer::reverseTransform($fieldType, $form->getData()['data']);
                $isValid = $this->dataService->isValid($formDataField, null, $object);

                if ($isValid || $form->isValid()) {
                    $this->dataService->getPostProcessing()->jsonMenuNested($formDataField, $contentType, $object);

                    $item = $this->jsonMenuNestedService->itemAdd($config, $parent, $node, $object);
                    $this->clearFlashes($request);

                    return JsonResponse::fromJsonString(AjaxModalResponse::success([
                        'load' => '_root' !== $parentId ? $parentId : null,
                        'added' => $item->getId(),
                    ]));
                }
            }

            return new JsonResponse($this->ajaxService
                ->ajaxModalTemplate(JsonMenuNestedTemplate::TWIG_TEMPLATE)
                ->render([
                    'form' => $form->createView(),
                    'node' => $node,
                    'item' => $parent,
                ])
            );
        } catch (JsonMenuNestedException|JsonMenuNestedConfigException $e) {
            return JsonResponse::fromJsonString(AjaxModalResponse::error(
                $this->translator->trans($e->getMessage(), [], EMSCoreBundle::TRANS_COMPONENT)
            ));
        }
    }

    public function itemDelete(Request $request, JsonMenuNestedConfig $config, string $itemId): JsonResponse
    {
        $result = $this->jsonMenuNestedService->itemDelete($config, $itemId);
        $this->clearFlashes($request);

        return new JsonResponse($result);
    }

    private function clearFlashes(Request $request): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->clear();
    }
}
