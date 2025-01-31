<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\File\TempFile;
use EMS\Helpers\Html\HtmlHelper;
use EMS\Helpers\Standard\Json;
use EMS\Xliff\Xliff\Entity\InsertReport;
use EMS\Xliff\Xliff\Extractor;
use EMS\Xliff\Xliff\Inserter;
use EMS\Xliff\Xliff\InsertionRevision;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class IntegratedTest extends TestCase
{
    public function testExtractInsert(): void
    {
        $finder = new Finder();
        $resourcesPath = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Integrated']);
        $finder->name('*.json')->in($resourcesPath.DIRECTORY_SEPARATOR.'sources');

        foreach ($finder as $file) {
            $basename = $file->getBasename('.json');
            [$ouuid, $revisionId] = \explode('_', $basename);
            $source = Json::decode(\file_get_contents($file->getPathname()));
            $target = Json::decode(\file_get_contents(\implode(DIRECTORY_SEPARATOR, [$resourcesPath, 'targets', $file->getBasename()])));
            $xliff = $this->generateXliff($ouuid, $revisionId, $source, $target);

            $xliffFilename = $this->saveAndCompare($file->getPath(), $xliff, $basename);

            $inserter = Inserter::fromFile($xliffFilename);
            $this->assertEquals(1, $inserter->count(), 'Only one document is expected');
            foreach ($inserter->getDocuments() as $document) {
                $this->insertDocument($document, $source, $target);
            }
        }
    }

    private function generateXliff(string $ouuid, string $revisionId, array $source, array $target): Extractor
    {
        $xliffParser = new Extractor('nl', 'de', Extractor::XLIFF_1_2);
        $document = $xliffParser->addDocument('content_type', $ouuid, $revisionId);
        foreach (['title', 'title_short'] as $field) {
            $xliffParser->addSimpleField($document, "[$field]", $source[$field] ?? null, $target[$field] ?? null, true);
        }
        foreach (['introduction', 'description'] as $field) {
            $xliffParser->addHtmlField($document, "[$field]", $source[$field] ?? null, $target[$field] ?? null, null, true);
        }

        return $xliffParser;
    }

    public function saveAndCompare(string $absoluteFilePath, Extractor $xliffParser, string $baseName): string
    {
        $expectedFilename = \implode(DIRECTORY_SEPARATOR, [$absoluteFilePath, '..', 'xliffs', $baseName.'.xlf']);
        if (!\file_exists($expectedFilename)) {
            $xliffParser->saveXML($expectedFilename);
        }

        $temp = TempFile::create();
        $tempFile = $temp->path;
        $xliffParser->saveXML($tempFile);

        $expected = \file_get_contents($expectedFilename);
        $actual = \file_get_contents($tempFile);

        $this->assertEquals($expected, $actual, \sprintf('testXliffExtractions: %s', $baseName));

        return $expectedFilename;
    }

    private function insertDocument(InsertionRevision $document, $source, $target)
    {
        $insertReport = new InsertReport();
        $inserted = $source;
        $document->extractTranslations($insertReport, $source, $inserted);
        $inserted['locale'] = 'de';

        foreach ($source as $field => $value) {
            if (\in_array($field, ['introduction', 'description'])) {
                $this->assertEquals(HtmlHelper::prettyPrint($inserted[$field]), HtmlHelper::prettyPrint($target[$field] ?? null), \sprintf('Field %s for inserted document : %s', $field, $document->getOuuid()));
            } else {
                $this->assertEquals($target[$field] ?? null, $inserted[$field], \sprintf('Field %s for inserted document : %s', $field, $document->getOuuid()));
            }
        }
    }

    public function testImportOnliner(): void
    {
        $inserter = Inserter::fromFile(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'new_extract.xlf']));
        $this->assertEquals(30, $inserter->count(), 'Only one document is expected');
        foreach ($inserter->getDocuments() as $document) {
            $this->saveJson($document);
        }
    }

    private function saveJson(InsertionRevision $document)
    {
        $source = Json::decode(\file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'TestRevision', \sprintf('%s_%s.json', $document->getOuuid(), $document->getRevisionId())])));
        $insertReport = new InsertReport();
        $inserted = $source;
        $document->extractTranslations($insertReport, $source, $inserted);
        unset($inserted['date_modification']);
        unset($inserted['_contenttype']);
        unset($inserted['_sha1']);
        unset($inserted['_published_datetime']);
        $inserted['locale'] = 'de';
        $filename = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'TestRevisionOut', \sprintf('%s_%s.json', $document->getOuuid(), $document->getRevisionId())]);
        if (!\file_exists($filename)) {
            \file_put_contents($filename, Json::encode($inserted, true));
        }

        $this->assertEquals(\file_get_contents($filename), Json::encode($inserted, true), \sprintf('with test file %s', $document->getOuuid()));
    }
}
