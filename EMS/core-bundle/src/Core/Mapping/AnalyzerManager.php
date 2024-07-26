<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Mapping;

use EMS\CommonBundle\Entity\EntityInterface;
use EMS\CoreBundle\Entity\Analyzer;
use EMS\CoreBundle\Repository\AnalyzerRepository;
use EMS\CoreBundle\Service\EntityServiceInterface;

class AnalyzerManager implements EntityServiceInterface
{
    public function __construct(private readonly AnalyzerRepository $analyzerRepository)
    {
    }

    public function count(string $searchValue = '', $context = null): int
    {
        return $this->analyzerRepository->counter($searchValue);
    }

    public function createEntityFromJson(string $json, ?string $name = null): EntityInterface
    {
        $analyzer = Analyzer::fromJson($json);
        if (null !== $name && $analyzer->getName() !== $name) {
            throw new \RuntimeException(\sprintf('Analyzer name mismatched: %s vs %s', $analyzer->getName(), $name));
        }
        $this->analyzerRepository->update($analyzer);

        return $analyzer;
    }

    public function deleteByItemName(string $name): string
    {
        $analyzer = $this->analyzerRepository->findByName($name);
        if (null === $analyzer) {
            throw new \RuntimeException(\sprintf('Analyzer %s not found', $name));
        }
        $id = $analyzer->getId();
        $this->analyzerRepository->delete($analyzer);

        return \strval($id);
    }

    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, $context = null): array
    {
        return $this->analyzerRepository->get($from, $size, $orderField, $orderDirection, $searchValue);
    }

    public function getAliasesName(): array
    {
        return [
            'analyzers',
            'Analyzer',
            'Analyzers',
            'analyser',
            'analysers',
            'Analyser',
            'Analysers',
        ];
    }

    public function getByItemName(string $name): ?EntityInterface
    {
        return $this->analyzerRepository->findByName($name);
    }

    public function getEntityName(): string
    {
        return 'analyzer';
    }

    public function isSortable(): bool
    {
        return true;
    }

    public function updateEntityFromJson(EntityInterface $entity, string $json): EntityInterface
    {
        $analyzer = Analyzer::fromJson($json, $entity);
        $this->analyzerRepository->update($analyzer);

        return $analyzer;
    }
}
