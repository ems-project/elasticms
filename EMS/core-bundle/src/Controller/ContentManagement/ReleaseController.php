<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\ContentManagement;

use EMS\CoreBundle\Core\DataTable\DataTableFactory;
use EMS\CoreBundle\DataTable\Type\Release\ReleaseOverviewDataTableType;
use EMS\CoreBundle\DataTable\Type\Release\ReleasePickDataTableType;
use EMS\CoreBundle\Entity\Release;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Form\Data\Condition\NotEmpty;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\QueryTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Form\Data\TemplateBlockTableColumn;
use EMS\CoreBundle\Form\Form\ReleaseType;
use EMS\CoreBundle\Form\Form\TableType;
use EMS\CoreBundle\Helper\DataTableRequest;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\ReleaseRevisionService;
use EMS\CoreBundle\Service\ReleaseService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReleaseController extends AbstractController
{
    public const ROLLBACK_ACTION = 'rollback_action';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ReleaseService $releaseService,
        private readonly ReleaseRevisionService $releaseRevisionService,
        private readonly DataTableFactory $dataTableFactory
    ) {
    }

    public function ajaxReleaseTableMemberRevisions(Request $request, Release $release): Response
    {
        $table = $this->getMemberRevisionsTable($release);
        $dataTableRequest = DataTableRequest::fromRequest($request);
        $table->resetIterator($dataTableRequest);

        return $this->render('@EMSCore/datatable/ajax.html.twig', [
            'dataTableRequest' => $dataTableRequest,
            'table' => $table,
        ], new JsonResponse());
    }

    public function ajaxReleaseTableNonMemberRevisions(Request $request, Release $release): Response
    {
        $table = $this->getNonMemberRevisionsTable($release);
        $dataTableRequest = DataTableRequest::fromRequest($request);
        $table->resetIterator($dataTableRequest);

        return $this->render('@EMSCore/datatable/ajax.html.twig', [
            'dataTableRequest' => $dataTableRequest,
            'table' => $table,
        ], new JsonResponse());
    }

    public function index(Request $request): Response
    {
        $table = $this->dataTableFactory->create(ReleaseOverviewDataTableType::class);
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form instanceof Form && ($action = $form->getClickedButton()) instanceof SubmitButton) {
                match ($action->getName()) {
                    TableAbstract::DELETE_ACTION => $this->releaseService->deleteByIds($table->getSelected()),
                    default => $this->logger->error('log.controller.release.unknown_action'),
                };
            } else {
                $this->logger->error('log.controller.release.unknown_action');
            }

            return $this->redirectToRoute(Routes::RELEASE_INDEX);
        }

        return $this->render('@EMSCore/release/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function add(Request $request): Response
    {
        $form = $this->createForm(ReleaseType::class, new Release(), [
            'add' => true,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $release = $this->releaseService->add($form->getViewData());

            return $this->redirectToRoute(Routes::RELEASE_EDIT, ['release' => $release->getId()]);
        }

        return $this->render('@EMSCore/release/add.html.twig', [
            'form_release' => $form->createView(),
        ]);
    }

    public function edit(Request $request, Release $release): Response
    {
        $revisionsTable = $this->getMemberRevisionsTable($release);

        $revisionsForm = $this->createForm(TableType::class, $revisionsTable, [
            'title_label' => 'release.revision.view.title',
        ]);
        $revisionsForm->handleRequest($request);
        if ($revisionsForm->isSubmitted() && $revisionsForm->isValid()) {
            if ($revisionsForm instanceof Form && ($action = $revisionsForm->getClickedButton()) instanceof SubmitButton) {
                match ($action->getName()) {
                    TableAbstract::REMOVE_ACTION => $this->releaseService->removeRevisions($release, $revisionsTable->getSelected()),
                    default => $this->logger->error('log.controller.release.unknown_action'),
                };
            } else {
                $this->logger->error('log.controller.release.unknown_action');
            }

            return $this->redirectToRoute(Routes::RELEASE_EDIT, ['release' => $release->getId()]);
        }

        $releaseForm = $this->createForm(ReleaseType::class, $release);
        $releaseForm->handleRequest($request);
        if ($releaseForm->isSubmitted() && $releaseForm->isValid()) {
            $this->releaseService->update($release);
            $saveAnbClose = $releaseForm->get('saveAndClose');
            if (!$saveAnbClose instanceof ClickableInterface) {
                throw new \RuntimeException('Unexpected non clickable object');
            }
            $nextAction = $saveAnbClose->isClicked() ? Routes::RELEASE_INDEX : Routes::RELEASE_EDIT;

            return $this->redirectToRoute($nextAction, ['release' => $release->getId()]);
        }

        return $this->render('@EMSCore/release/edit.html.twig', [
            'form' => $revisionsForm->createView(),
            'form_release' => $releaseForm->createView(),
            'release' => $release,
        ]);
    }

    public function view(Request $request, Release $release): Response
    {
        $table = $this->getMemberRevisionsTable($release);
        $form = $this->createForm(TableType::class, $table, [
            'title_label' => 'release.revision.view.title',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form instanceof Form && ($action = $form->getClickedButton()) instanceof SubmitButton) {
                switch ($action->getName()) {
                    case self::ROLLBACK_ACTION:
                        $rollback = $this->releaseService->rollback($release, $table->getSelected());

                        return $this->redirectToRoute(Routes::RELEASE_EDIT, ['release' => $rollback->getId()]);
                    default:
                        $this->logger->error('log.controller.release.unknown_action');
                }
            } else {
                $this->logger->error('log.controller.release.unknown_action');
            }

            return $this->redirectToRoute(Routes::RELEASE_VIEW, ['release' => $release->getId()]);
        }

        return $this->render('@EMSCore/release/view.html.twig', [
            'form' => $form->createView(),
            'release' => $release,
        ]);
    }

    public function delete(Release $release): Response
    {
        $this->releaseService->delete($release);

        return $this->redirectToRoute(Routes::RELEASE_INDEX);
    }

    public function changeStatus(Release $release, string $status): Response
    {
        $release->setStatus($status);
        $this->releaseService->update($release);

        return $this->redirectToRoute(Routes::RELEASE_INDEX);
    }

    public function addRevisionById(Release $release, Revision $revision): Response
    {
        $this->releaseService->addRevision($release, $revision);

        return $this->redirectToRoute(Routes::VIEW_REVISIONS, [
            'type' => $revision->giveContentType()->getName(),
            'ouuid' => $revision->getOuuid(),
            'revisionId' => $revision->getId(),
        ]);
    }

    public function addRevision(Release $release, string $emsLinkToAdd): Response
    {
        $this->releaseService->addRevisions($release, [$emsLinkToAdd]);

        return $this->redirectToRoute(Routes::RELEASE_EDIT, ['release' => $release->getId()]);
    }

    public function addRevisions(Request $request, Release $release): Response
    {
        $table = $this->getNonMemberRevisionsTable($release);
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form instanceof Form && ($action = $form->getClickedButton()) instanceof SubmitButton) {
                match ($action->getName()) {
                    TableAbstract::ADD_ACTION => $this->releaseService->addRevisions($release, $table->getSelected()),
                    default => $this->logger->error('log.controller.release.unknown_action'),
                };
            } else {
                $this->logger->error('log.controller.release.unknown_action');
            }

            return $this->redirectToRoute(Routes::RELEASE_EDIT, ['release' => $release->getId()]);
        }

        return $this->render('@EMSCore/release/revisions.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function releasePublish(Release $release): Response
    {
        $this->releaseService->publishRelease($release);

        return $this->redirectToRoute(Routes::RELEASE_INDEX);
    }

    public function pickRelease(Revision $revision): Response
    {
        $table = $this->dataTableFactory->create(ReleasePickDataTableType::class, [
            'revision_id' => $revision->getId(),
        ]);

        $form = $this->createForm(TableType::class, $table);

        return $this->render('@EMSCore/release/add-to-release.html.twig', [
            'form' => $form->createView(),
            'revision' => $revision,
        ]);
    }

    private function getMemberRevisionsTable(Release $release): EntityTable
    {
        $table = new EntityTable($this->releaseRevisionService, $this->generateUrl(Routes::RELEASE_MEMBER_REVISION_AJAX, ['release' => $release->getId()]), $release);
        $table->setMassAction(true);
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.CT', 'contentType', '@EMSCore/release/columns/release-revisions.html.twig'));
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.document', 'label', '@EMSCore/release/columns/release-revisions.html.twig'));
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.revision', 'revision', '@EMSCore/release/columns/release-revisions.html.twig'));
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.action', 'action', '@EMSCore/release/columns/release-revisions.html.twig'));

        switch ($release->getStatus()) {
            case Release::WIP_STATUS:
                $table->addTableAction(TableAbstract::REMOVE_ACTION, 'fa fa-minus', 'release.revision.actions.remove', 'release.revision.actions.remove_confirm');
                break;
            case Release::APPLIED_STATUS:
                $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.still_in_target', 'stil_in_target', '@EMSCore/release/columns/release-revisions.html.twig'))->setLabelTransOption(['%target%' => $release->getEnvironmentTarget()->getLabel()]);
                $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.previous', 'previous', '@EMSCore/release/columns/release-revisions.html.twig'));
                $table->addDynamicItemGetAction(Routes::VIEW_REVISIONS, 'release.revision.index.column.compare', 'compress', ['type' => 'contentType', 'ouuid' => 'revisionOuuid', 'revisionId' => 'revision.id', 'compareId' => 'revisionBeforePublish.id'])->addCondition(new NotEmpty('revisionBeforePublish', 'revision'));
                $table->addTableAction(self::ROLLBACK_ACTION, 'fa fa-rotate-left', 'release.revision.table.rollback.action', 'release.revision.table.rollback.confirm');
                break;
        }

        return $table;
    }

    private function getNonMemberRevisionsTable(Release $release): QueryTable
    {
        $table = new QueryTable($this->releaseRevisionService, 'revisions-to-publish', $this->generateUrl(Routes::RELEASE_NON_MEMBER_REVISION_AJAX, ['release' => $release->getId()]), $release);
        $table->setMassAction(true);
        $table->setLabelAttribute('item_labelField');
        $table->setIdField('emsLink');
        $table->addColumn('release.revision.index.column.label', 'item_labelField');
        $table->addColumn('release.revision.index.column.CT', 'content_type_singular_name');
        $table->setSelected($release->getRevisionsOuuids());
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.minRevId', 'minrevid', '@EMSCore/release/columns/revisions.html.twig'));
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.maxRevId', 'maxrevid', '@EMSCore/release/columns/revisions.html.twig'));
        $table->addTableAction(TableAbstract::ADD_ACTION, 'fa fa-plus', 'release.revision.actions.add', 'release.revision.actions.add_confirm');
        $table->addDynamicItemPostAction(Routes::RELEASE_ADD_REVISION, 'release.revision.action.add', 'plus', 'release.revision.actions.add_confirm', ['release' => \sprintf('%d', $release->getId()), 'emsLinkToAdd' => 'emsLink']);

        return $table;
    }
}
