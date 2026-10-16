<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EMS\CoreBundle\Entity\ChoiceList;

/**
 * @extends ServiceEntityRepository<ChoiceList>
 *
 * @method ChoiceList|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChoiceList|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method ChoiceList[]    findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
class ChoiceListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChoiceList::class);
    }

    /**
     * @return ChoiceList[]
     */
    public function getAll(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    public function create(ChoiceList $choiceList): void
    {
        $this->getEntityManager()->persist($choiceList);
        $this->getEntityManager()->flush();
    }

    public function update(ChoiceList $choiceList): void
    {
        $this->getEntityManager()->persist($choiceList);
        $this->getEntityManager()->flush();
    }

    public function delete(ChoiceList $choiceList): void
    {
        $this->getEntityManager()->remove($choiceList);
        $this->getEntityManager()->flush();
    }

    public function getById(string $id): ChoiceList
    {
        if (null === $choiceList = $this->find($id)) {
            throw new \RuntimeException('Choice list not found');
        }

        return $choiceList;
    }

    public function getByName(string $name): ?ChoiceList
    {
        return $this->findOneBy(['name' => $name]);
    }
}
