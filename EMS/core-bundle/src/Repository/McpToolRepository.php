<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use EMS\CoreBundle\Entity\McpTool;

/**
 * @extends ServiceEntityRepository<McpTool>
 *
 * @method McpTool|null find($id, $lockMode = null, $lockVersion = null)
 * @method McpTool|null findOneBy(mixed[] $criteria, mixed[] $orderBy = null)
 * @method McpTool[]    findBy(mixed[] $criteria, mixed[] $orderBy = null, $limit = null, $offset = null)
 */
final class McpToolRepository extends ServiceEntityRepository
{
    public function __construct(Registry $registry)
    {
        parent::__construct($registry, McpTool::class);
    }

    public function create(McpTool $mcpTool): void
    {
        $this->getEntityManager()->persist($mcpTool);
        $this->getEntityManager()->flush();
    }

    /**
     * @return McpTool[]
     */
    public function findEnabled(): array
    {
        return $this->findBy(['enabled' => true], ['name' => 'ASC']);
    }

    public function getByName(string $name): ?McpTool
    {
        return $this->findOneBy(['name' => $name]);
    }
}
