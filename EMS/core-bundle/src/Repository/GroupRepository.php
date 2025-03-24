<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use EMS\CoreBundle\Entity\Group;

class GroupRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    public function getAll(): array
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->getQuery()->execute();
    }

    public function save(Group $group): void
    {
        $existingGroup = $this->getEntityManager()
            ->getRepository(Group::class)
            ->findOneBy(['name' => $group->getName()]);

        if ($existingGroup) {
            throw new \Exception('The group with this name already exists.');
        }
        $this->getEntityManager()->persist($group);
        $this->getEntityManager()->flush();
    }

    public function delete(Group $group): void
    {
        $this->getEntityManager()->remove($group);
        $this->getEntityManager()->flush();
    }

    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue)
    {
        $qb = $this->createQueryBuilder('c')
            ->setFirstResult($from)
            ->setMaxResults($size);

        if (\in_array($orderField, ['name', 'label'])) {
            $qb->orderBy(\sprintf('c.%s', $orderField), $orderDirection);
        } else {
            $qb->orderBy('c.name', $orderDirection);
        }

        return $qb->getQuery()->execute();
    }

    public function counter(string $searchValue = ''): int
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('count(c.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function deleteAllGroup(): void
    {
        $em = $this->createQueryBuilder('g');
        $em->delete(Group::class, 'c')
            ->getQuery()
            ->execute();
    }

    public function edit(Group $group): void
    {
        $this->getEntityManager()->persist($group);
        $this->getEntityManager()->flush();
    }

    public function getByName(string $name): ?Group
    {
        $qb = $this->createQueryBuilder('user_group');
        $qb
            ->andWhere($qb->expr()->eq('user_group.name', ':name'))
            ->setParameter('name', $name);

        $userGroup = $qb->getQuery()->getOneOrNullResult();

        return $userGroup instanceof Group ? $userGroup : null;
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
}
