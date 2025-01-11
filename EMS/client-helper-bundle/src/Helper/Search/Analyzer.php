<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Search;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;

final readonly class Analyzer
{
    public function __construct(private ClientRequest $clientRequest)
    {
    }

    /**
     * @param string[]  $tokens
     * @param Synonym[] $synonyms
     *
     * @return TextValue[]
     */
    public function getTextValues(string $field, string $analyzer, array $tokens, array $synonyms = []): array
    {
        $textValues = [];

        foreach ($tokens as $token) {
            $textValue = new TextValue($token, $field, $analyzer);

            $this->addSynonyms($textValue, $synonyms);

            $textValues[$token] = $textValue;
        }

        return $textValues;
    }

    /**
     * @param Synonym[] $synonyms
     */
    private function addSynonyms(TextValue $textValue, array $synonyms = []): void
    {
        foreach ($synonyms as $synonym) {
            $queryText = $textValue->getQuery($synonym->getSearchField(), $textValue->getAnalyzer());
            $querySynonym = $synonym->getQuery($queryText);

            $body = ['_source' => ['_contenttype'], 'query' => $querySynonym->toArray()];
            $documents = $this->clientRequest->search([], $body, 0, 20);

            if ($documents['hits']['total'] > 20) {
                continue;
            }

            $field = $synonym->getField();
            if (null === $field) {
                continue;
            }

            foreach ($documents['hits']['hits'] as $doc) {
                $textValue->addSynonym($field, $doc);
            }
        }
    }
}
