<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Search;

use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\MatchPhrase;
use Elastica\Query\QueryString;

/**
 * If we search for 'foo bar'
 * the SearchManager will create two SearchValue instances.
 */
final class TextValue
{
    private string $text;
    private string $field;
    private string $analyzer;
    /** @var AbstractQuery[] */
    private array $synonyms;

    public function __construct(string $text, string $field, string $analyzer)
    {
        $this->text = $text;
        $this->field = $field;
        $this->analyzer = $analyzer;
        $this->synonyms = [];
    }

    public function getAnalyzer(): string
    {
        return $this->analyzer;
    }

    /**
     * @param array<string, mixed> $doc
     */
    public function addSynonym(string $synonymField, array $doc): void
    {
        $contentType = $doc['_source']['_contenttype'];
        $ouuid = $doc['_id'];
        if (!\is_string($contentType) || !\is_string($ouuid)) {
            throw new \RuntimeException('Wrong document structure');
        }
        $match = new Match($synonymField);
        $match->setFieldQuery($synonymField, \sprintf('%s:%s', $contentType, $ouuid));
        $match->setFieldOperator($synonymField, 'AND');
        $this->synonyms[] = $match;
    }

    public function makeShould(float $boost = 1.0): AbstractQuery
    {
        $should = new BoolQuery();
        $should->addShould($this->getQuery($this->field, $this->analyzer, $boost));

        foreach ($this->synonyms as $synonym) {
            $should->addShould($synonym);
        }

        return $should;
    }

    public function getQuery(string $field, string $analyzer, float $boost = 1.0): AbstractQuery
    {
        $matches = [];
        \preg_match_all('/^\"(.*)\"$/', $this->text, $matches);

        if (isset($matches[1][0])) {
            $matchPhrase = new MatchPhrase($field);
            $matchPhrase->setFieldAnalyzer($field, $analyzer);
            $matchPhrase->setFieldQuery($field, $matches[1][0]);
            $matchPhrase->setFieldBoost($field, $boost);

            return $matchPhrase;
        }

        if (false !== \strpos($this->text, '*')) {
            $queryString = new QueryString($this->text);
            $queryString->setDefaultField($field);
            $queryString->setAnalyzer($analyzer);
            $queryString->setAnalyzeWildcard();
            $queryString->setBoost($boost);

            return $queryString;
        }

        $match = new Match($field);
        $match->setFieldQuery($field, $this->text);
        $match->setFieldBoost($field, $boost);

        return $match;
    }
}
