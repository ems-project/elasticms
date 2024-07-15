<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\Revision;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Core\DataTable\Type\AbstractTableType;
use EMS\CoreBundle\Core\DataTable\Type\QueryServiceTypeInterface;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Form\Data\DatetimeTableColumn;
use EMS\CoreBundle\Form\Data\QueryTable;
use EMS\CoreBundle\Form\Data\UserTableColumn;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use EMS\Helpers\Standard\Json;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

class RevisionTrashDataTableType extends AbstractTableType implements QueryServiceTypeInterface
{
    public const ACTION_EMPTY_TRASH = 'empty-trash';
    public const ACTION_PUT_BACK = 'put-back';

    public function __construct(
        private readonly Registry $doctrine,
        private readonly RevisionService $revisionService,
        private readonly UserService $userService,
        private readonly ContentTypeService $contentTypeService,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function build(QueryTable $table): void
    {
        /** @var ContentType $contentType */
        $contentType = $table->getContext()['content_type'];

        $table->setIdField('ouuid');
        $table->setExtraFrontendOption(['searching' => false]);

        if ($this->userService->isSuper()) {
            $table->addColumn(t('revision.field.ouuid', [], 'emsco-core'), 'ouuid');
        }

        $table->addColumn(t('field.label', [], 'emsco-core'), 'revision_label');
        $table->addColumnDefinition(new UserTableColumn(t('field.deleted_by', [], 'emsco-core'), 'deleted_by'));
        $table->addColumnDefinition(new DatetimeTableColumn(t('field.modified', [], 'emsco-core'), 'modified'));

        $table->setLabelAttribute('revision_label');

        if ($this->authorizationChecker->isGranted($contentType->role(ContentTypeRoles::CREATE))) {
            $table->addDynamicItemPostAction(
                route: Routes::DATA_TRASH_PUT_BACK,
                labelKey: t('revision.trash.put_back', [], 'emsco-core'),
                icon: 'recycle',
                messageKey: t('revision.trash.put_back_confirm', [], 'emsco-core'),
                routeParameters: [
                    'contentType' => 'content_type_id',
                    'ouuid' => 'ouuid',
                ]
            );
            $table->addTableAction(
                name: self::ACTION_PUT_BACK,
                icon: 'fa fa-recycle',
                labelKey: t('revision.trash.put_back_selected', [], 'emsco-core')
            );
        }

        $table->addDynamicItemPostAction(
            route: Routes::DATA_TRASH_EMPTY,
            labelKey: t('table.delete', [], 'emsco-core'),
            icon: 'trash',
            messageKey: t('revision.trash.delete_confirm', [], 'emsco-core'),
            routeParameters: [
                'contentType' => 'content_type_id',
                'ouuid' => 'ouuid',
            ]
        )->setButtonType('outline-danger');
        $table->addTableAction(
            name: self::ACTION_EMPTY_TRASH,
            icon: 'fa fa-trash',
            labelKey: t('table.delete_selected', [], 'emsco-core')
        )->setCssClass('btn btn-outline-danger');
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setRequired(['content_type_name']);
    }

    /**
     * @param array{'content_type_name': string} $options
     *
     * @return array{'content_type': ContentType, 'deleted'?: bool, 'current'?: bool}
     */
    public function getContext(array $options): array
    {
        return [
            'content_type' => $this->contentTypeService->giveByName($options['content_type_name']),
            'deleted' => true,
            'current' => true,
        ];
    }

    public function getQueryName(): string
    {
        return 'revision_trash';
    }

    public function isQuerySortable(): bool
    {
        return false;
    }

    /**
     * @param array{'content_type'?: ContentType, 'deleted'?: bool, 'current'?: bool}|null $context
     *
     * @return array<array<string, mixed>>
     */
    public function query(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        $qb = $this->createQueryBuilder($context ?? [])
            ->addSelect('r.id as id')
            ->addSelect('r.modified as modified')
            ->addSelect('r.deleted_by as deleted_by')
            ->addSelect('r.ouuid as ouuid')
            ->addSelect('r.raw_data as raw_data')
            ->addSelect('c.id as content_type_id')
            ->addSelect('c.name as content_type_name')
            ->setMaxResults($size)
            ->setFirstResult($from);

        if ($orderField) {
            $qb->orderBy($orderField, $orderDirection);
        }

        $results = $qb->executeQuery()->fetchAllAssociative();

        foreach ($results as &$result) {
            [$contentTypeName, $ouuid] = [$result['content_type_name'], $result['ouuid']];
            $document = Document::fromData($contentTypeName, $ouuid, Json::decode($result['raw_data']));
            $result['revision_label'] = $this->revisionService->display($document);
        }

        return $results;
    }

    /**
     * @param array{'content_type'?: ContentType, 'deleted'?: bool, 'current'?: bool}|null $context
     */
    public function countQuery(string $searchValue = '', mixed $context = null): int
    {
        return (int) $this->createQueryBuilder($context ?? [])->select('count(r.id)')->fetchOne();
    }

    /**
     * @param array{'content_type'?: ContentType, 'deleted'?: bool, 'current'?: bool} $context
     */
    private function createQueryBuilder(array $context): QueryBuilder
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $qb = $em->getConnection()->createQueryBuilder();

        $qb = $qb
            ->from('revision', 'r')
            ->join('r', 'content_type', 'c', 'r.content_type_id = c.id');

        foreach ($context as $key => $value) {
            match ($key) {
                'content_type' => $qb
                    ->andWhere($qb->expr()->eq('c.id', ':content_type_id'))
                    ->setParameter('content_type_id', $value->getId()),
                'current' => $qb->andWhere($qb->expr()->isNull('r.end_time')),
                'deleted' => $qb->andWhere($qb->expr()->eq('r.deleted', $qb->expr()->literal($value)))
            };
        }

        return $qb;
    }
}
