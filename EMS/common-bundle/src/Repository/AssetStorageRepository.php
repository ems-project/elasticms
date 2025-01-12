<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use EMS\CommonBundle\Entity\AssetStorage;

/**
 * @extends EntityRepository<AssetStorage>
 */
class AssetStorageRepository extends EntityRepository
{
    private function getQuery(string $hash, bool $confirmed): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where($qb->expr()->eq('a.hash', ':hash'));
        $qb->andWhere($qb->expr()->eq('a.confirmed', ':confirmed'));
        $qb->setParameters([
            ':hash' => $hash,
            ':confirmed' => $confirmed,
        ]);

        return $qb;
    }

    public function head(string $hash, bool $confirmed = true): bool
    {
        try {
            $qb = $this->getQuery($hash, $confirmed)->select('count(a.hash)');

            return 0 !== $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException) {
            return false;
        }
    }

    public function removeByHash(string $hash): bool
    {
        try {
            $qb = $this->createQueryBuilder('asset')->delete();
            $qb->where($qb->expr()->eq('asset.hash', ':hash'));
            $qb->setParameter(':hash', $hash, Types::STRING);

            return false !== $qb->getQuery()->execute();
        } catch (\Throwable) {
            return false;
        }
    }

    public function findByHash(string $hash, bool $confirmed = true): ?AssetStorage
    {
        $qb = $this->getQuery($hash, $confirmed)->select('a');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function getSize(string $hash, bool $confirmed = true): ?int
    {
        try {
            $qb = $this->getQuery($hash, $confirmed)->select('a.size');

            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function delete(AssetStorage $assetStorage): void
    {
        $this->getEntityManager()->remove($assetStorage);
        $this->getEntityManager()->flush();
    }
}
