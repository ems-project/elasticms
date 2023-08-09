<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\Release;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Entity\Release;
use EMS\CoreBundle\Form\Data\Condition\NotEmpty;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Form\Data\TemplateBlockTableColumn;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\ReleaseRevisionService;
use EMS\CoreBundle\Service\ReleaseService;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleaseRevisionDataTableType extends AbstractEntityTableType
{
    public function __construct(
        ReleaseRevisionService $releaseRevisionService,
        private readonly ReleaseService $releaseService
    ) {
        parent::__construct($releaseRevisionService);
    }

    public function build(EntityTable $table): void
    {
        /** @var Release $release */
        $release = $table->getContext();

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
                $table->addTableAction('rollback_action', 'fa fa-rotate-left', 'release.revision.table.rollback.action', 'release.revision.table.rollback.confirm');
                break;
        }
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_PUBLISHER];
    }

    public function getContext(array $options): Release
    {
        return $this->releaseService->getById($options['release_id']);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setRequired(['release_id']);
    }
}
