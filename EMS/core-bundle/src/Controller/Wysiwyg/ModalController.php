<?php

namespace EMS\CoreBundle\Controller\Wysiwyg;

use EMS\CommonBundle\Common\EMSLink;
use EMS\CoreBundle\Core\UI\FlashMessageLogger;
use EMS\CoreBundle\Form\Form\LoadLinkModalType;
use EMS\CoreBundle\Service\Revision\RevisionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class ModalController extends AbstractController
{
    public function __construct(
        private readonly RevisionService $revisionService,
        private readonly Environment $twig,
        private readonly FlashMessageLogger $flashMessageLogger,
        private readonly string $templateNamespace,
    ) {
    }

    public function loadLinkModal(Request $request): JsonResponse
    {
        $url = (string) $request->request->get('url', '');
        if (\str_starts_with($url, 'ems://')) {
            $data = [
                'dataLink' => EMSLink::fromText($url)->getEmsId(),
            ];
        } else {
            $data = [
                'href' => $url,
            ];
        }
        $form = $this->createForm(LoadLinkModalType::class, $data);

        return $this->flashMessageLogger->buildJsonResponse([
            'body' => $this->twig->render("@$this->templateNamespace/modal/link.html.twig", [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function emsLinkInfo(Request $request): JsonResponse
    {
        $link = $request->query->get('link');
        if (!\is_string($link)) {
            return $this->flashMessageLogger->buildJsonResponse([]);
        }
        $emsLink = EMSLink::fromText($link);
        $revision = $this->revisionService->get($emsLink->getOuuid(), $emsLink->getContentType());
        if (!\is_string($link)) {
            return $this->flashMessageLogger->buildJsonResponse([]);
        }

        return $this->flashMessageLogger->buildJsonResponse([
            'label' => null === $revision ? $emsLink->getOuuid() : $revision->getLabel(),
        ]);
    }
}
