<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Environment;

use EMS\CommonBundle\Entity\EntityInterface;
use EMS\CoreBundle\Repository\EnvironmentRepository;
use EMS\CoreBundle\Service\EntityServiceInterface;

class EnvironmentManagedEntityService implements EntityServiceInterface
{
    public function __construct(private readonly EnvironmentRepository $environmentRepository)
    {
    }

    public function isSortable(): bool
    {
        return true;
    }

    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, $context = null): array
    {
        return $this->environmentRepository->get(
            from: $from,
            size: $size,
            orderField: $orderField,
            orderDirection: $orderDirection,
            searchValue: $searchValue,
            isManaged: true
        );
    }

    public function getEntityName(): string
    {
        return 'environment_managed';
    }

    public function getAliasesName(): array
    {
        return [];
    }

    public function count(string $searchValue = '', $context = null): int
    {
        return $this->environmentRepository->counter(searchValue: $searchValue, isManaged: true);
    }

    public function getByItemName(string $name): ?EntityInterface
    {
        return $this->environmentRepository->findOneById($name);
    }

    public function updateEntityFromJson(EntityInterface $entity, string $json): EntityInterface
    {
        throw new \RuntimeException('updateEntityFromJson method not yet implemented');
    }

    public function createEntityFromJson(string $json, ?string $name = null): EntityInterface
    {
        throw new \RuntimeException('createEntityFromJson method not yet implemented');
    }

    public function deleteByItemName(string $name): string
    {
        throw new \RuntimeException('deleteByItemName method not yet implemented');
    }
}
