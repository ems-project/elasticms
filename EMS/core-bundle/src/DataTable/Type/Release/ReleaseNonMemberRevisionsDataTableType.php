<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\Release;

use EMS\CoreBundle\Core\DataTable\Type\AbstractQueryTableType;
use EMS\CoreBundle\Entity\Release;
use EMS\CoreBundle\Form\Data\QueryTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Form\Data\TemplateBlockTableColumn;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\ReleaseRevisionService;
use EMS\CoreBundle\Service\ReleaseService;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleaseNonMemberRevisionsDataTableType extends AbstractQueryTableType
{
    public function __construct(
        ReleaseRevisionService $releaseRevisionService,
        private readonly ReleaseService $releaseService,
        private readonly string $templateNamespace
    ) {
        parent::__construct($releaseRevisionService);
    }

    public function build(QueryTable $table): void
    {
        /** @var Release $release */
        $release = $table->getContext();

        $table->setMassAction(true);
        $table->setLabelAttribute('item_labelField');
        $table->setIdField('emsLink');
        $table->setSelected($release->getRevisionsOuuids());

        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.label', 'label', "@$this->templateNamespace/release/columns/revisions.html.twig"));
        $table->addColumn('release.revision.index.column.CT', 'content_type_singular_name');
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.minRevId', 'minrevid', "@$this->templateNamespace/release/columns/revisions.html.twig"));
        $table->addColumnDefinition(new TemplateBlockTableColumn('release.revision.index.column.maxRevId', 'maxrevid', "@$this->templateNamespace/release/columns/revisions.html.twig"));

        $table->addTableAction(TableAbstract::ADD_ACTION, 'fa fa-plus', 'release.revision.actions.add', 'release.revision.actions.add_confirm');
        $table->addDynamicItemPostAction(Routes::RELEASE_ADD_REVISION, 'release.revision.action.add', 'plus', 'release.revision.actions.add_confirm', ['release' => \sprintf('%d', $release->getId()), 'emsLinkToAdd' => 'emsLink']);
    }

    public function getQueryName(): string
    {
        return 'revisions-to-publish';
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
