<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Search;

use Elastica\Aggregation\Terms;
use Elastica\Query\MatchAll;
use Elastica\Suggest;
use EMS\CommonBundle\Search\Search;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class SearchAiTest extends TestCase
{
    public function testSerializeAndDeserialize(): void
    {
        $search = new Search(['index1'], new MatchAll());
        $serialized = $search->serialize();

        $deserialized = Search::deserialize($serialized);

        $this->assertInstanceOf(Search::class, $deserialized);
        $this->assertEquals($search->getIndices(), $deserialized->getIndices());
    }

    public function testHasSources(): void
    {
        $search = new Search(['index1']);
        $this->assertFalse($search->hasSources());

        $search->setSources(['field1']);
        $this->assertTrue($search->hasSources());
    }

    public function testGetSources(): void
    {
        $search = new Search(['index1']);
        $search->setSources(['field1', 'field2']);
        $this->assertEquals(['field1', 'field2', '_contenttype', '_version_uuid', '_sha1'], $search->getSources());
    }

    public function testGetQueryArray(): void
    {
        $search = new Search(['index1'], ['match_all' => new \stdClass()]);
        $this->assertEquals(['match_all' => new \stdClass()], $search->getQueryArray());
    }

    public function testSetQueryArray(): void
    {
        $search = new Search(['index1']);
        $search->setQueryArray(['match_all' => new \stdClass()]);
        $this->assertEquals(['match_all' => new \stdClass()], $search->getQueryArray());
    }

    public function testAddAggregations(): void
    {
        $search = new Search(['index1']);
        $termsAggregation = new Terms('test_aggregation');
        $search->addAggregations([$termsAggregation]);

        $aggregations = $search->getAggregations();
        $this->assertCount(1, $aggregations);
        $this->assertInstanceOf(Terms::class, $aggregations[0]);
    }

    public function testAddTermsAggregation(): void
    {
        $search = new Search(['index1']);
        $search->addTermsAggregation('test_aggregation', 'field1', 10);

        $aggregations = $search->getAggregations();
        $this->assertCount(1, $aggregations);
        $this->assertInstanceOf(Terms::class, $aggregations[0]);
    }

    public function testSetAndGetIndices(): void
    {
        $search = new Search(['index1']);
        $this->assertEquals(['index1'], $search->getIndices());
    }

    public function testSetAndGetContentTypes(): void
    {
        $search = new Search(['index1']);
        $search->setContentTypes(['type1', 'type2']);
        $this->assertEquals(['type1', 'type2'], $search->getContentTypes());
    }

    public function testSetAndGetFrom(): void
    {
        $search = new Search(['index1']);
        $search->setFrom(5);
        $this->assertEquals(5, $search->getSearchOptions()[\Elastica\Search::OPTION_FROM]);
    }

    public function testSetAndGetSize(): void
    {
        $search = new Search(['index1']);
        $search->setSize(15);
        $this->assertEquals(15, $search->getSearchOptions()[\Elastica\Search::OPTION_SIZE]);
    }

    public function testSetAndGetSort(): void
    {
        $search = new Search(['index1']);
        $search->setSort(['field1' => 'asc']);
        $this->assertEquals(['field1' => 'asc'], $search->getSort());
    }

    public function testSetAndGetPostFilter(): void
    {
        $search = new Search(['index1']);
        $postFilter = new MatchAll();
        $search->setPostFilter($postFilter);
        $this->assertEquals($postFilter, $search->getPostFilter());
    }

    public function testSetAndGetSuggest(): void
    {
        $search = new Search(['index1']);
        $suggest = new Suggest();
        $search->setSuggest($suggest);
        $this->assertEquals($suggest, $search->getSuggest());
    }

    public function testSetAndGetHighlight(): void
    {
        $search = new Search(['index1']);
        $highlight = ['field' => 'field1'];
        $search->setHighlight($highlight);
        $this->assertEquals($highlight, $search->getHighlight());
    }

    public function testSetAndGetRegex(): void
    {
        $search = new Search(['index1']);
        $search->setRegex('test_regex');
        $this->assertEquals('test_regex', $search->getRegex());
    }

    public function testGetSerializer(): void
    {
        $serializer = Search::getSerializer();
        $this->assertInstanceOf(Serializer::class, $serializer);
    }
}
