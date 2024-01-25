<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Component;

use EMS\CoreBundle\Core\Component\MediaLibrary\Config\MediaLibraryConfig;
use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryService;
use EMS\CoreBundle\Core\Component\MediaLibrary\Request\MediaLibraryRequest;
use EMS\CoreBundle\Core\UI\AjaxModal;
use EMS\CoreBundle\Core\UI\AjaxService;
use EMS\CoreBundle\EMSCoreBundle;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryController
{
    public function __construct(
        private readonly MediaLibraryService $mediaLibraryService,
        private readonly AjaxService $ajax,
        private readonly TranslatorInterface $translator,
        private readonly FormFactory $formFactory,
        private readonly string $templateNamespace
    ) {
    }

    public function getHeader(MediaLibraryConfig $config, Request $request, ?string $folderId = null): JsonResponse
    {
        return new JsonResponse([
            'header' => $this->mediaLibraryService->renderHeader(
                config: $config,
                folder: $folderId,
                fileIds: $request->query->all('files')
            ),
        ]);
    }

    public function getFiles(MediaLibraryConfig $config, MediaLibraryRequest $request): JsonResponse
    {
        return new JsonResponse($this->mediaLibraryService->getFiles($config, $request));
    }

    public function getFolders(MediaLibraryConfig $config): JsonResponse
    {
        return new JsonResponse($this->mediaLibraryService->getFolders($config));
    }

    public function addFolder(MediaLibraryConfig $config, MediaLibraryRequest $request): JsonResponse
    {
        $form = $this->formFactory->createBuilder(FormType::class, [])
            ->add('folder_name', TextType::class, ['constraints' => [new NotBlank()]])
            ->getForm();

        $form->handleRequest($request->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $folderName = (string) $form->get('folder_name')->getData();
            $folder = $this->mediaLibraryService->createFolder($config, $request, $folderName);

            if ($folder) {
                $request->clearFlashes();

                return $this->getAjaxModal()->getSuccessResponse(['path' => $folder->path]);
            }
        }

        return $this
            ->getAjaxModal()
            ->setTitle($this->translator->trans('media_library.folder.add.title', [], EMSCoreBundle::TRANS_COMPONENT))
            ->setBody('bodyAddFolder', ['form' => $form->createView()])
            ->setFooter('footerAddFolder')
            ->getResponse();
    }

    public function addFile(MediaLibraryConfig $config, MediaLibraryRequest $request): JsonResponse
    {
        if (!$this->mediaLibraryService->createFile($config, $request)) {
            return new JsonResponse([
                'messages' => $request->getFlashes(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $request->clearFlashes();

        return new JsonResponse([], Response::HTTP_CREATED);
    }

    public function renameFile(MediaLibraryConfig $config, Request $request, string $fileId): JsonResponse
    {
        $mediaFile = $this->mediaLibraryService->getFile($config, $fileId);

        $form = $this->formFactory->createBuilder(FormType::class, $mediaFile)
            ->add('name', TextType::class, ['constraints' => [new NotBlank()]])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mediaLibraryService->updateFile($config, $mediaFile);
            $this->clearFlashes($request);

            return new JsonResponse([
                'success' => true,
                'fileRow' => $this->mediaLibraryService->renderFileRow($config, $mediaFile),
            ]);
        }

        $modal = $this->mediaLibraryService->modal($config, [
            'type' => 'rename',
            'title' => $this->translator->trans('media_library.file.rename.title', [], EMSCoreBundle::TRANS_COMPONENT),
            'form' => $form->createView(),
        ]);

        return new JsonResponse($modal->render());
    }

    public function renameFolder(MediaLibraryConfig $config, Request $request, string $folderId): JsonResponse
    {
        $mediaFolder = $this->mediaLibraryService->getFolder($config, $folderId);

        $form = $this->formFactory->createBuilder(FormType::class, $mediaFolder)
            ->add('name', TextType::class, ['constraints' => [new NotBlank()]])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->clearFlashes($request);

            return new JsonResponse(['success' => true, 'folderName' => $mediaFolder->getName()]);
        }

        $modal = $this->mediaLibraryService->modal($config, [
            'type' => 'rename',
            'title' => $this->translator->trans('media_library.folder.rename.title', [], EMSCoreBundle::TRANS_COMPONENT),
            'form' => $form->createView(),
        ]);

        return new JsonResponse($modal->render());
    }

    public function deleteFiles(MediaLibraryConfig $config, Request $request): JsonResponse
    {
        $fileIds = Json::decode($request->getContent())['files'];

        $success = $this->mediaLibraryService->deleteFiles($config, $fileIds);
        $this->clearFlashes($request);

        return new JsonResponse(['success' => $success]);
    }

    private function getAjaxModal(): AjaxModal
    {
        return $this->ajax->newAjaxModel("@$this->templateNamespace/components/media_library/modal.html.twig");
    }

    private function clearFlashes(Request $request): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->clear();
    }
}
