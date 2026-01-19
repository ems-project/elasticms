<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\File\TempFile;
use EMS\Xliff\Xliff\Entity\InsertReport;
use EMS\Xliff\Xliff\Extractor;
use EMS\Xliff\Xliff\Inserter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EncodeDecodeAndTest extends TestCase
{
    /**
     * @return array<array<int|string>>
     */
    public static function htmlProvider(): array
    {
        return [[
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-1', 'source.html'])),
            \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-1', 'expected.xlf']),
        ], [
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-2', 'source.html'])),
            \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-2', 'expected.xlf']),
        ], [
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-3', 'source.html'])),
            \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-3', 'expected.xlf']),
        ]];
    }

    #[DataProvider('htmlProvider')]
    public function testWithBaseline(string $sourceHtml, string $expectedPath): void
    {
        $xliffParser = new Extractor('en', 'fr', Extractor::XLIFF_1_2);
        $document = $xliffParser->addDocument('content_type', 'fakeOuuid', 'fakeRevisionId');
        $xliffParser->addHtmlField($document, '[body]', $sourceHtml);
        $insertReport = new InsertReport();

        if (!\file_exists($expectedPath)) {
            $xliffParser->saveXML($expectedPath);
        }
        $expected = \file_get_contents($expectedPath);

        $tempFile = TempFile::create();
        $xliffParser->saveXML($tempFile->path);
        $extracted = \file_get_contents($tempFile->path);
        $this->assertSame($expected, $extracted);
        $tempFile->clean();

        $importer = Inserter::fromFile($expectedPath);
        foreach ($importer->getDocuments() as $document) {
            $this->assertSame('fakeOuuid', $document->getOuuid());
            $this->assertSame('content_type', $document->getContentType());
            $this->assertSame('fakeRevisionId', $document->getRevisionId());
            $correspondingJson = [
                'body' => $sourceHtml,
            ];
            $target = [];
            $document->extractTranslations($insertReport, $correspondingJson, $target);
            $this->assertSame(0, $insertReport->countErrors(), 'Errors in extract translations');
        }
    }
}
