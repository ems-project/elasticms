<?php

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EMS\CoreBundle\Core\User\UserList;
use EMS\CoreBundle\Entity\User;
class GroupRepository extends ServiceEntityRepository 
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, User::class);
    }

  
    public function getAll(): array
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->getQuery()->execute();
    }
}