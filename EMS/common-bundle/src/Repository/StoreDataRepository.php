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
 * @method StoreData|null findOneBy(array $criteria, array $orderBy = null)
 * @method StoreData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
}
