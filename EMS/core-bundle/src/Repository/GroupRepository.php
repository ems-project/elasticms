<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
