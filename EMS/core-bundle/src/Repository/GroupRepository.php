<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\QueryBuilder;
use EMS\CoreBundle\Entity\Group;
use EMS\CoreBundle\Entity\Schedule;

class GroupRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * @return Schedule[]
     */
    public function getAll(): array
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->getQuery()->execute();
    }

    public function save(Group $group,bool $isEditMode): void
    {
        $existingGroup = $this->getEntityManager()
            ->getRepository(Group::class)
            ->findOneBy(['name' => $group->getName()]);

        if ($existingGroup) {
            if ($isEditMode) {
                if ($existingGroup->getId() !== $group->getId()) {
                    throw new \Exception('The group already exists.');
                }else{
                    $this->getEntityManager()->persist($group);
                    $this->getEntityManager()->flush();
                }
            }
        }else{
            $this->getEntityManager()->persist($group);
            $this->getEntityManager()->flush();
        }
    }

    public function delete(Group $group): void
    {
        $this->getEntityManager()->remove($group);
        $this->getEntityManager()->flush();
    }

    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue)
    {
        $qb = $this->createQueryBuilder('g')
            ->setFirstResult($from)
            ->setMaxResults($size);
        $this->addSearchFilters($qb, $searchValue);

        if (\in_array($orderField, ['name', 'label'])) {
            $qb->orderBy(\sprintf('g.%s', $orderField), $orderDirection);
        } else {
            $qb->orderBy('g.name', $orderDirection);
        }

        return $qb->getQuery()->execute();
    }
    private function addSearchFilters(QueryBuilder $qb, string $searchValue): void
    {
        if (\strlen($searchValue) > 0) {
            $or = $qb->expr()->orX(
                $qb->expr()->like('c.label', ':term'),
                $qb->expr()->like('c.name', ':term'),
            );
            $qb->andWhere($or)
                ->setParameter(':term', '%'.$searchValue.'%');
        }
    }

    public function counter(string $searchValue = ''): int
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('count(c.id)');
        $this->addSearchFilters($qb, $searchValue);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function deleteAllGroup(): void
    {
        $em = $this->createQueryBuilder('g');
        $em->delete(Group::class, 'c')
            ->getQuery()
            ->execute();
    }

    public function getByName(string $name): ?Group
    {
        $qb = $this->createQueryBuilder('user_group');
        $qb
            ->andWhere($qb->expr()->eq('user_group.name', ':name'))
            ->setParameter('name', $name);

        $userGroup = $qb->getQuery()->getOneOrNullResult();

        if (null !== $userGroup && ! $userGroup instanceof Group) {
            throw new \RuntimeException('Unexpected Group entity');
        }

        return $userGroup;
    }

    /**
     * @param string[] $ids
     *
     * @return Group[]
     */
    public function getByIds(array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('user_group');
        $queryBuilder->where('user_group.id IN (:ids)')
            ->setParameter('ids', $ids, ArrayParameterType::STRING);

        return $queryBuilder->getQuery()->getResult();
    }

    public function deleteGroupByIds(array $ids): void
    {
        $queryBuilder = $this->createQueryBuilder('g');
        $queryBuilder
            ->delete(Group::class, 'g') 
            ->where('g.id IN (:ids)')   
            ->setParameter('ids', $ids) 
            ->getQuery()
            ->execute();
    }
}
