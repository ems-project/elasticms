<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Xliff\Xliff\Entity\InsertReport;
use EMS\Xliff\Xliff\Extractor;
use EMS\Xliff\Xliff\Inserter;
use PHPUnit\Framework\TestCase;

class BaselineTest extends TestCase
{
    public function testLoadBaseline2(): void
    {
        $inserter = Inserter::fromFile(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2.xlf']));
        foreach ($inserter->getDocuments() as $document) {
            $this->assertEquals('9055abe4a93f3f7e435cc96860116c274fd52f2c', $document->getOuuid());
            $this->assertEquals('1018373', $document->getRevisionId());

            $insertReport = new InsertReport();
            $extractedRawData = [];
            $insertRawData = [];
            $document->extractTranslations($insertReport, $extractedRawData, $insertRawData);
            $this->assertEquals('Lohn für Arbeitsanfänger', $insertRawData['title'] ?? null);
            $this->assertEquals('Lohn für Arbeitsanfänger', $insertRawData['title_short'] ?? null);
            //            \file_put_contents(\join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2_description.html']), $insertRawData['description']);
            //            \file_put_contents(\join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2_introduction.html']), $insertRawData['introduction']);
            $this->assertEquals(\file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2_description.html'])), $insertRawData['description'] ?? null);
            $this->assertEquals(\file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2_introduction.html'])), $insertRawData['introduction'] ?? null);
        }
    }

    public function testBaseline1(): void
    {
        $source = \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline1_source.html']));
        $target = \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline1_target.html']));

        $xliffParser = new Extractor('nl', 'de', Extractor::XLIFF_1_2);
        $document = $xliffParser->addDocument('contentType', 'ouuid_1', 'revisionId_1');
        $xliffParser->addHtmlField($document, '[%locale%][body]', $source, $target, null, true);

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
