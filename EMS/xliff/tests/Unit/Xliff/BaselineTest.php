<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Xliff\Xliff\Extractor;
use PHPUnit\Framework\TestCase;

class BaselineTest extends TestCase
{
    public function testBaseline1(): void
    {
        $source = \file_get_contents(\join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline1_source.html']));
        $target = \file_get_contents(\join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline1_target.html']));

        $xliffParser = new Extractor('nl', 'de', Extractor::XLIFF_1_2);
        $document = $xliffParser->addDocument('contentType', 'ouuid_1', 'revisionId_1');
        $xliffParser->addHtmlField($document, '[%locale%][body]', $source, $target, true);

        $sources = $xliffParser->getDom()->getElementsByTagName('target');
        $this->assertEquals(36, $sources->count());
        foreach ($sources as $item) {
            if (!$item instanceof \DOMElement) {
                throw new \RuntimeException('Unexpected non \DOMElement item');
            }
            $this->assertEquals('final', $item->getAttribute('state'));
        }
    }
}
