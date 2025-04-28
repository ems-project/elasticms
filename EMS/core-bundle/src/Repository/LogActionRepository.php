<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EMS\CoreBundle\Entity\LogAction;

/**
 * @extends ServiceEntityRepository<LogAction>
 *
 * @method LogAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogAction|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method LogAction[]    findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class LogActionRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, LogAction::class);
    }

    public function save(LogAction $logAction): void
    {
        $this->getEntityManager()->persist($logAction);
        $this->getEntityManager()->flush();
    }
}
