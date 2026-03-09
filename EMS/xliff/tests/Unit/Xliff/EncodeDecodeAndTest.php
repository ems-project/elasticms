<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\File\TempFile;
use EMS\Helpers\Html\HtmlHelper;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\Xliff;
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
        ], [
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-4', 'source.html'])),
            \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-4', 'expected.xlf']),
        ], [
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-5', 'source.html'])),
            \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'EncodeDecode', 'TC-5', 'expected.xlf']),
        ]];
    }

    #[DataProvider('htmlProvider')]
    public function testWithBaseline(string $sourceHtml, string $expectedPath): void
    {
        $option = new Options(Version::V12);
        $readerPackage = Xliff::create($option);
        $readerPackage->init('en', 'fr');

        $document = $readerPackage->getPackage()->addDocument('content_type:fakeOuuid:fakeRevisionId');
        $document->createHtml('[body]', $sourceHtml, $sourceHtml, $sourceHtml);

        if (!\file_exists($expectedPath)) {
            $readerPackage->saveXML($expectedPath);
        }
        $expected = \file_get_contents($expectedPath);

        $tempFile = TempFile::create();
        $readerPackage->saveXML($tempFile->path);
        $extracted = \file_get_contents($tempFile->path);
        $this->assertSame($expected, $extracted);
        $tempFile->clean();

        $readerPackage->fromFile($expectedPath);
        foreach ($readerPackage->getPackage()->getDocuments() as $document) {
            [$contentType, $ouuid, $revisionId] = \explode(':', $document->id);
            $this->assertSame('fakeOuuid', $ouuid);
            $this->assertSame('content_type', $contentType);
            $this->assertSame('fakeRevisionId', $revisionId);
            $correspondingJson = [
                'body' => $sourceHtml,
            ];
            $target = [];
            $document->unitToAssociativeArray($readerPackage->getPackage(), $correspondingJson, $target);
            $this->assertSame(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($sourceHtml)), $target['body']);
        }
        $this->assertSame(0, $readerPackage->getPackage()->getInsertReport()->countErrors(), 'Errors in extract translations');
    }
}
