<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Nested;
use Elastica\Result;
use EMS\CommonBundle\Elasticsearch\Response\AnalyzeResponse;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\Helpers\Standard\Hash;
use Twig\Extension\RuntimeExtensionInterface;

final class SearchRuntime implements RuntimeExtensionInterface
{
    /** @var array<mixed> */
    private array $nestedCache = [];

    public function __construct(private readonly ElasticaService $elasticaService)
    {
    }

    /**
     * @param string|string[]       $contentTypeNames
     * @param array<string, string> $search
     *
     * @return array<mixed>
     */
    public function nestedSearch(string $alias, string|array $contentTypeNames, string $nestedFieldName, array $search): array
    {
        $choices = $this->getNestedSearchChoices($alias, $contentTypeNames, $nestedFieldName);

        return \array_values(\array_filter($choices, function (array $choice) use ($search) {
            foreach ($search as $searchKey => $searchValue) {
                if (!isset($choice[$searchKey]) || $choice[$searchKey] !== $searchValue) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @param array<string, string|string[]> $parameters
     */
    public function analyze(string $text, array $parameters, ?string $index = null): AnalyzeResponse
    {
        return $this->elasticaService->analyze($text, $parameters, $index);
    }

    /**
     * @param string|string[] $contentTypeNames
     *
     * @return array<mixed>
     */
    private function getNestedSearchChoices(string $alias, string|array $contentTypeNames, string $nestedFieldName): array
    {
        $contentTypeNames = \is_string($contentTypeNames) ? [$contentTypeNames] : $contentTypeNames;
        $cacheKey = Hash::array([$alias, ...$contentTypeNames, $nestedFieldName]);

        if (isset($this->nestedCache[$cacheKey])) {
            return $this->nestedCache[$cacheKey];
        }

        $choices = [];

        foreach ($this->nestedSearchScroll($alias, $contentTypeNames, $nestedFieldName) as $result) {
            $choices = \array_merge($choices, $result->getSource()[$nestedFieldName]);
        }

        $this->nestedCache[$cacheKey] = $choices;

        return $choices;
    }

    /**
     * @param string[] $contentTypeNames
     *
     * @return \Generator<Result>
     */
    private function nestedSearchScroll(string $alias, array $contentTypeNames, string $field): \Generator
    {
        $nestedQuery = new Nested();
        $nestedQuery->setPath($field)->setQuery((new BoolQuery())->addMust(new Exists($field)));

        $search = new Search([$alias], (new BoolQuery())->addMust($nestedQuery));
        $search->setSources([$field]);
        $search->setContentTypes($contentTypeNames);

        $scroll = $this->elasticaService->scroll($search);

        foreach ($scroll as $resultSet) {
            foreach ($resultSet as $result) {
                yield $result;
            }
        }
    }
}
