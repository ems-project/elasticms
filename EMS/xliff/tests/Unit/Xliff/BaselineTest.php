<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\Xliff;
use PHPUnit\Framework\TestCase;

class BaselineTest extends TestCase
{
    public function testLoadBaseline2(): void
    {
        $readerPackage = Xliff::create();
        $readerPackage->fromFile(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2.xlf']));

        $insertReport = $readerPackage->getPackage()->getInsertReport();
        foreach ($readerPackage->getPackage()->getDocuments() as $document) {
            [$contentType, $uuid, $revisionId] = \explode(':', $document->id);
            $this->assertEquals('instruction', $contentType);
            $this->assertEquals('9055abe4a93f3f7e435cc96860116c274fd52f2c', $uuid);
            $this->assertEquals('1018373', $revisionId);

            $extractedRawData = [];
            $insertRawData = [];
            $document->unitToAssociativeArray($readerPackage->getPackage(), $extractedRawData, $insertRawData);
            $this->assertEquals('Lohn für Arbeitsanfänger', $insertRawData['title'] ?? null);
            $this->assertEquals('Lohn für Arbeitsanfänger', $insertRawData['title_short'] ?? null);
            $this->assertEquals(\file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2_description.html'])), $insertRawData['description'] ?? null);
            $this->assertEquals(\file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline2_introduction.html'])), $insertRawData['introduction'] ?? null);
        }
        $this->assertSame(1, $insertReport->countErrors());
    }

    public function testBaseline1(): void
    {
        $source = \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline1_source.html']));
        $target = \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Baseline', 'baseline1_target.html']));

        $option = new Options(Version::V12);
        $readerPackage = Xliff::create($option);
        $readerPackage->init('nl', 'de');

        $document = $readerPackage->getPackage()->addDocument('contentType:ouuid_1:revisionId_1');
        $document->createHtml('[%locale%][body]', $source, $target, null, true);

        $counter = 0;
        foreach ($document->getSegments() as $segment) {
            $this->assertEquals('final', $segment->getState());
            ++$counter;
        }
        $this->assertEquals(36, $counter);
    }
}
