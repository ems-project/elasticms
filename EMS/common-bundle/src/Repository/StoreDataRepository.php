<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EMS\CommonBundle\Entity\StoreData;

/**
 * @extends ServiceEntityRepository<StoreData>
 *
 * @method StoreData|null find($id, $lockMode = null, $lockVersion = null)
 * @method StoreData|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method StoreData[]    findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
final class StoreDataRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, StoreData::class);
    }

    public function getByKey(string $key): ?StoreData
    {
        return $this->findOneBy(['key' => $key]);
    }

    public function update(StoreData $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function delete(StoreData $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function deleteExpired(): int
    {
        $qb = $this->createQueryBuilder('store_data')->delete();
        $qb->where($qb->expr()->isNotNull('store_data.expiresAt'));
        $qb->andWhere($qb->expr()->lt('store_data.expiresAt', ':now'));
        $qb->setParameters([
            ':now' => new \DateTimeImmutable(),
        ]);

        return \intval($qb->getQuery()->execute());
    }
}
