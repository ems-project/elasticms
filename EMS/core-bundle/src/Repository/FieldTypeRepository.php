<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EMS\CoreBundle\Entity\FieldType;

/**
 * @extends ServiceEntityRepository<FieldType>
 *
 * @method FieldType|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldType|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method FieldType[]    findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class FieldTypeRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, FieldType::class);
    }

    public function save(FieldType $field): void
    {
        $this->getEntityManager()->persist($field);
        $this->getEntityManager()->flush();
    }
}
