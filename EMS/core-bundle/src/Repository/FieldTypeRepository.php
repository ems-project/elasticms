<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EMS\CoreBundle\Entity\FieldType;

/**
 * @extends ServiceEntityRepository<FieldType>
 */
class FieldTypeRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, FieldType::class);
    }

    /**
     * @return FieldType[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->findBy(['deleted' => false]);
    }

    public function save(FieldType $field): void
    {
        $this->_em->persist($field);
        $this->_em->flush();
    }
}
