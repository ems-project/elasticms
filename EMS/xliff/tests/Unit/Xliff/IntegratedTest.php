<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\File\TempFile;
use EMS\Helpers\Html\HtmlHelper;
use EMS\Helpers\Standard\Json;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\Xliff;
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

            $xliff = Xliff::create();
            $xliff->fromFile($xliffFilename);
            $this->assertCount(1, $xliff->getPackage()->getDocuments(), 'Only one document is expected');
            foreach ($xliff->getPackage()->getDocuments() as $document) {
                $this->insertDocument($xliff->getPackage(), $document, $source, $target);
            }
        }
    }

    private function generateXliff(string $ouuid, string $revisionId, array $source, array $target): Xliff
    {
        $xliff = Xliff::create(new Options(Version::V12));
        $xliff->init('nl', 'de');

        $document = $xliff->getPackage()->addDocument("content_type:$ouuid:$revisionId");
        foreach (['title', 'title_short'] as $field) {
            $document->createText("[$field]", $source[$field] ?? null, $target[$field] ?? null, null, true);
        }
        foreach (['introduction', 'description'] as $field) {
            $document->createHtml("[$field]", $source[$field] ?? '', $target[$field] ?? null, null, true);
        }

        return $xliff;
    }

    public function saveAndCompare(string $absoluteFilePath, Xliff $xliff, string $baseName): string
    {
        $expectedFilename = \implode(DIRECTORY_SEPARATOR, [$absoluteFilePath, '..', 'xliffs', $baseName.'.xlf']);
        if (!\file_exists($expectedFilename)) {
            $xliff->saveXML($expectedFilename);
        }

        $temp = TempFile::create();
        $tempFile = $temp->path;
        $xliff->saveXML($tempFile);

        $expected = \file_get_contents($expectedFilename);
        $actual = \file_get_contents($tempFile);

        $this->assertEquals($expected, $actual, \sprintf('testXliffExtractions: %s', $baseName));

        return $expectedFilename;
    }

    private function insertDocument(Package $package, Document $document, array $source, array $target)
    {
        [$contentType, $ouuid, $revisionId] = \explode(':', $document->id);
        $inserted = $source;
        $document->unitToAssociativeArray($package, $source, $inserted);
        $inserted['locale'] = 'de';

        foreach (\array_keys($source) as $field) {
            if (\in_array($field, ['introduction', 'description'])) {
                $this->assertEquals(HtmlHelper::prettyPrint($inserted[$field]), HtmlHelper::prettyPrint($target[$field] ?? null), \sprintf('Field %s for inserted document : %s', $field, $ouuid));
            } else {
                $this->assertEquals($target[$field] ?? null, $inserted[$field], \sprintf('Field %s for inserted document : %s', $field, $ouuid));
            }
        }
    }

    public function testImportOnliner(): void
    {
        $xliff = Xliff::create();
        $xliff->fromFile(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'new_extract.xlf']));
        $this->assertCount(30, $xliff->getPackage()->getDocuments(), '30 documents are expected');
        foreach ($xliff->getPackage()->getDocuments() as $document) {
            $this->saveJson($xliff->getPackage(), $document);
        }
    }

    private function saveJson(Package $package, Document $document)
    {
        [$contentType, $ouuid, $revisionId] = \explode(':', $document->id);
        $source = Json::decode(\file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'TestRevision', \sprintf('%s_%s.json', $ouuid, $revisionId)])));
        $inserted = $source;
        $document->unitToAssociativeArray($package, $source, $inserted);
        unset($inserted['date_modification']);
        unset($inserted['_contenttype']);
        unset($inserted['_sha1']);
        unset($inserted['_published_datetime']);
        $inserted['locale'] = 'de';
        $filename = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'TestRevisionOut', \sprintf('%s_%s.json', $ouuid, $revisionId)]);
        if (!\file_exists($filename)) {
            \file_put_contents($filename, Json::encode($inserted, true));
        }

        $this->assertEquals(\file_get_contents($filename), Json::encode($inserted, true), \sprintf('with test file %s', $ouuid));
    }
}
