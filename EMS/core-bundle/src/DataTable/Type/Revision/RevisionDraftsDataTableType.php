<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\Revision;

use Doctrine\ORM\QueryBuilder;
use EMS\CoreBundle\Core\DataTable\Type\AbstractTableType;
use EMS\CoreBundle\Core\DataTable\Type\QueryServiceTypeInterface;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Form\Data\Condition\DateInFuture;
use EMS\CoreBundle\Form\Data\Condition\InMyCircles;
use EMS\CoreBundle\Form\Data\Condition\NotEmpty;
use EMS\CoreBundle\Form\Data\DatetimeTableColumn;
use EMS\CoreBundle\Form\Data\QueryTable;
use EMS\CoreBundle\Form\Data\UserTableColumn;
use EMS\CoreBundle\Repository\RevisionRepository;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\UserService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RevisionDraftsDataTableType extends AbstractTableType implements QueryServiceTypeInterface
{
    final public const DISCARD_SELECTED_DRAFT = 'DISCARD_SELECTED_DRAFT';

    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ContentTypeService $contentTypeService,
        private readonly UserService $userService
    ) {
    }

    public function build(QueryTable $table): void
    {
        /** @var ContentType $contentType */
        $contentType = $table->getContext();

        $table->setRowActionsClass('pull-right');
        $table->setLabelAttribute('label');
        $table->setDefaultOrder('modified', 'desc');
        $table->addColumnDefinition(new DatetimeTableColumn('revision.draft-in-progress.column.modified', 'draftSaveDate'));
        $table->addColumnDefinition(new UserTableColumn('revision.draft-in-progress.column.auto-save-by', 'autoSaveBy'));
        $table->addColumn('revision.draft-in-progress.column.label', 'label')->setOrderField('labelField');
        $lockUntil = new DatetimeTableColumn('revision.draft-in-progress.column.locked', 'lockUntil');
        $condition = new DateInFuture('lockUntil');
        $lockUntil->addCondition($condition);
        $table->addColumnDefinition($lockUntil);
        $lockBy = new UserTableColumn('revision.draft-in-progress.column.locked-by', 'lockBy');
        $lockBy->addCondition($condition);
        $table->addColumnDefinition($lockBy);
        $inMyCircles = new InMyCircles($this->userService);
        $table->addDynamicItemGetAction(Routes::EDIT_REVISION, 'revision.draft-in-progress.column.edit-draft', 'pencil', [
            'revisionId' => 'id',
        ])->addCondition($inMyCircles)->setButtonType('primary');
        $table->addDynamicItemGetAction(Routes::VIEW_REVISIONS, 'revision.draft-in-progress.column.view-revision', '', [
            'type' => 'contentType.name',
            'ouuid' => 'ouuid',
        ])->addCondition(new NotEmpty('ouuid'));
        $table->addDynamicItemPostAction(Routes::DISCARD_DRAFT, 'revision.draft-in-progress.column.discard-draft', 'trash', 'revision.draft-in-progress.column.confirm-discard-draft', [
            'revisionId' => 'id',
        ])->addCondition($inMyCircles)->setButtonType('outline-danger');

        if (null !== $contentType && (null === $contentType->getCirclesField() || '' === $contentType->getCirclesField())) {
            $table->addTableAction(self::DISCARD_SELECTED_DRAFT, 'fa fa-trash', 'revision.draft-in-progress.action.discard-selected-draft', 'revision.draft-in-progress.action.discard-selected-confirm')
                ->setCssClass('btn btn-outline-danger');
        }
    }

    public function getContext(array $options): ContentType
    {
        return $this->contentTypeService->giveByName($options['content_type_name']);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setRequired(['content_type_name']);
    }

    public function getQueryName(): string
    {
        return 'draft_in_progress';
    }

    public function isQuerySortable(): bool
    {
        return false;
    }

    public function query(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        if (null !== $context && !$context instanceof ContentType) {
            throw new \RuntimeException('Unexpected context');
        }

        $qb = $this->createQueryBuilder($searchValue);
        $qb
            ->setFirstResult($from)
            ->setMaxResults($size);

        if (null !== $context) {
            $qb->andWhere($qb->expr()->eq('c.id', ':content_type_id'));
            $qb->setParameter('content_type_id', $context->getId());
        }

        if (null !== $orderField) {
            $qb->orderBy(\sprintf('r.%s', $orderField), $orderDirection);
        }

        return $qb->getQuery()->execute();
    }

    public function countQuery(string $searchValue = '', mixed $context = null): int
    {
        if (null !== $context && !$context instanceof ContentType) {
            throw new \RuntimeException('Unexpected context');
        }

        $qb = $this->createQueryBuilder($searchValue);
        $qb->select('count(r.id)');

        if (null !== $context) {
            $qb->andWhere($qb->expr()->eq('c.id', ':content_type_id'));
            $qb->setParameter('content_type_id', $context->getId());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function createQueryBuilder(string $searchValue = ''): QueryBuilder
    {
        return $this->revisionRepository->createQueryBuilderDrafts(
            circles: $this->userService->getCurrentUser()->getCircles(),
            isAdmin: $this->authorizationChecker->isGranted('ROLE_ADMIN'),
            searchValue: $searchValue
        );
    }
}
