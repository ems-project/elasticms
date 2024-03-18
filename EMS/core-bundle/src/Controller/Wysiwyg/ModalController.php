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
                LoadLinkModalType::FIELD_DATA_LINK => EMSLink::fromText($url)->getEmsId(),
                LoadLinkModalType::FIELD_LINK_TYPE => LoadLinkModalType::LINK_TYPE_INTERNAL,
            ];
        } elseif (\str_starts_with($url, 'mailto:')) {
            \preg_match('/mailto:(?P<mailto>.*)\?(?P<query>.*)?/', $url, $matches);
            \parse_str($matches['query'] ?? '', $query);
            $data = [
                LoadLinkModalType::FIELD_MAILTO => $matches['mailto'] ?? '',
                LoadLinkModalType::FIELD_SUBJECT => $query['subject'] ?? '',
                LoadLinkModalType::FIELD_BODY => $query['body'] ?? '',
                LoadLinkModalType::FIELD_LINK_TYPE => LoadLinkModalType::LINK_TYPE_MAILTO,
            ];
        } else {
            $data = [
                LoadLinkModalType::FIELD_HREF => $url,
                LoadLinkModalType::FIELD_LINK_TYPE => LoadLinkModalType::LINK_TYPE_URL,
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
