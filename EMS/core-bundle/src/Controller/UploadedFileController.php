<?php

namespace EMS\CoreBundle\Controller;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use EMS\CoreBundle\Core\DataTable\DataTableFactory;
use EMS\CoreBundle\DataTable\Type\UploadedAsset\UploadedAssetDataTableType;
use EMS\CoreBundle\DataTable\Type\UploadedFileLogDataTableType;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Form\Form\TableType;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

class UploadedFileController extends AbstractController
{
    use CoreControllerTrait;

    /** @var string */
    final public const SOFT_DELETE_ACTION = 'soft_delete';
    /** @var string */
    final public const HIDE_ACTION = 'hide';

    public function __construct(
        private readonly LocalizedLoggerInterface $logger,
        private readonly FileService $fileService,
        private readonly DataTableFactory $dataTableFactory,
        private readonly string $templateNamespace
    ) {
    }

    public function index(Request $request): Response
    {
        $table = $this->dataTableFactory->create(UploadedAssetDataTableType::class, [
            'location' => UploadedAssetDataTableType::LOCATION_PUBLISHER_OVERVIEW,
            'roles' => [Roles::ROLE_PUBLISHER],
        ]);

        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return match ($this->getClickedButtonName($form)) {
                TableAbstract::DOWNLOAD_ACTION => (function () use ($table) {
                    $ids = $this->fileService->hashesToIds($table->getSelected());

                    return $this->downloadMultiple($ids);
                })(),
                UploadedAssetDataTableType::HIDE_ACTION => (function () use ($table) {
                    $this->fileService->hideByHashes($table->getSelected());

                    return $this->redirectToRoute('ems_core_uploaded_file_index');
                })(),
                default => (function () {
                    $this->logger->messageError(t('log.error.invalid_table_action', [], 'emsco-core'));

                    return $this->redirectToRoute('ems_core_uploaded_file_index');
                })()
            };
        }

        return $this->render("@$this->templateNamespace/crud/overview.html.twig", [
            'form' => $form->createView(),
            'icon' => 'fa fa-upload',
            'title' => t('key.uploaded_files', [], 'emsco-core'),
            'breadcrumb' => [
                'publishers' => t('key.publishers', [], 'emsco-core'),
                'page' => t('key.uploaded_files', [], 'emsco-core'),
            ],
        ]);
    }

    public function logs(Request $request): Response
    {
        $table = $this->dataTableFactory->create(UploadedFileLogDataTableType::class);

        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form instanceof Form && ($action = $form->getClickedButton()) instanceof SubmitButton) {
                switch ($action->getName()) {
                    case TableAbstract::DOWNLOAD_ACTION:
                        return $this->downloadMultiple($table->getSelected());
                    case self::HIDE_ACTION:
                        if (!$this->isGranted('ROLE_PUBLISHER')) {
                            throw new AccessDeniedException($request->getPathInfo());
                        }
                        $this->fileService->toggleFileEntitiesVisibility($table->getSelected());
                        break;
                    case self::SOFT_DELETE_ACTION:
                        if (!$this->isGranted('ROLE_ADMIN')) {
                            throw new AccessDeniedException($request->getPathInfo());
                        }
                        $this->fileService->removeSingleFileEntity($table->getSelected());
                        break;
                }
            } else {
                $this->logger->error('log.controller.uploaded-file-logs.unknown_action');
            }

            return $this->redirectToRoute('ems_core_uploaded_file_logs');
        }

        return $this->render("@$this->templateNamespace/uploaded-file-logs/index.html.twig", [
            'form' => $form->createView(),
        ]);
    }

    public function hideByHash(Request $request, string $hash): Response
    {
        if (!$this->isGranted('ROLE_PUBLISHER')) {
            throw new AccessDeniedException($request->getPathInfo());
        }
        $this->fileService->hideByHashes([$hash]);

        return $this->redirectToRoute('ems_core_uploaded_file_index');
    }

    public function showHide(Request $request, string $assetId): Response
    {
        if (!$this->isGranted('ROLE_PUBLISHER')) {
            throw new AccessDeniedException($request->getPathInfo());
        }
        $this->fileService->toggleFileEntitiesVisibility([$assetId]);

        return $this->redirectToRoute('ems_core_uploaded_file_logs');
    }

    /**
     * @param array<string> $fileIds
     */
    private function downloadMultiple(array $fileIds): Response
    {
        try {
            $response = $this->fileService->createDownloadForMultiple($fileIds);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            return $this->redirectToRoute('ems_core_uploaded_file_index');
        }

        return $response;
    }
}
