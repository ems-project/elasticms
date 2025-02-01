<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\File\TempFile;
use EMS\Xliff\Xliff\Entity\InsertReport;
use EMS\Xliff\Xliff\Extractor;
use EMS\Xliff\Xliff\Inserter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class ExtractorTest extends TestCase
{
    public function testBrInEmptyParagraph(): void
    {
        $rawData = [
            'nl' => [
                'body' => "<p>
    Deze doelgroepvermindering is in elke regio aangepast en/of
    vervangen. U vindt meer op de desbetreffende pagina's.
    </p>
    <p>
    <br>
    </p>
    <p>
    EOD
    </p>",
            ],
        ];

        $existing = [
            'de' => [
                'body' => "<p>
    [de] Deze doelgroepvermindering is in elke regio aangepast en/of
    vervangen. U vindt meer op de desbetreffende pagina's.
    </p>
    <p>
    <br>
    </p>
    <p>
    [de] EOD
    </p>",
            ],
        ];

        $extracted = [];

        $xliffParser = new Extractor('nl', 'de', Extractor::XLIFF_1_2);
        $document = $xliffParser->addDocument('contentType', 'ouuid_1', 'revisionId_1');
        $xliffParser->addHtmlField($document, '[%locale%][body]', $rawData['nl']['body'], $existing['de']['body'], null);
        $insertReport = new InsertReport();

        $inserter = new Inserter($xliffParser->getDom());
        foreach ($inserter->getDocuments() as $document) {
            $document->extractTranslations($insertReport, $rawData, $extracted);
        }
        $this->assertEquals([
            'de' => [
                'body' => "<p>
  [de] Deze doelgroepvermindering is in elke regio aangepast
  en/of vervangen. U vindt meer op de desbetreffende pagina's.
</p>
<p>
  <br>
</p>
<p>
  [de] EOD
</p>",
            ],
        ], $extracted);
    }

    public function testXliffExtractions(): void
    {
        $finder = new Finder();
        $finder->in(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Extractions']))->directories();

        foreach ($finder as $file) {
            $absoluteFilePath = $file->getRealPath();
            $fileNameWithExtension = $file->getRelativePathname();
            $htmlSource = \file_get_contents($absoluteFilePath.DIRECTORY_SEPARATOR.'source.html');
            $this->assertNotFalse($htmlSource);
            $htmlTarget = null;
            if (\file_exists($absoluteFilePath.DIRECTORY_SEPARATOR.'target.html')) {
                $htmlTarget = \file_get_contents($absoluteFilePath.DIRECTORY_SEPARATOR.'target.html');
            }

            foreach (Extractor::XLIFF_VERSIONS as $version) {
                $xliffParser = new Extractor('en', 'fr', $version);
                $document = $xliffParser->addDocument('contentType', 'ouuid_1', 'revisionId_1');
                $xliffParser->addSimpleField($document, '[title_%locale%]', 'Foo', 'Bar');
                $document = $xliffParser->addDocument('contentType', 'ouuid_2', 'revisionId_2');
                $xliffParser->addSimpleField($document, '[title_%locale%]', 'Hello', 'Bonjour');
                $xliffParser->addSimpleField($document, '[keywords_%locale%]', 'test xliff');
                $xliffParser->addSimpleField($document, '[empty]', '', null, true);
                $xliffParser->addHtmlField($document, '[%locale%][body]', $htmlSource, $htmlTarget ?: null, null);
                $xliffParser->addHtmlField($document, '[%locale%][body2]', $htmlSource, $htmlTarget ?: null, null, true);

                $this->saveAndCompare($absoluteFilePath, $version, $xliffParser, $fileNameWithExtension, 'UTF-8');
                $this->saveAndCompare($absoluteFilePath, $version, $xliffParser, $fileNameWithExtension, 'us-ascii');
            }
        }
    }

    public function saveAndCompare(string $absoluteFilePath, string $version, Extractor $xliffParser, string $fileNameWithExtension, string $encoding): void
    {
        $expectedFilename = $absoluteFilePath.DIRECTORY_SEPARATOR.'expected_'.$encoding.$version.'.xlf';
        if (!\file_exists($expectedFilename)) {
            $xliffParser->saveXML($expectedFilename, $encoding);
        }

        $tempFile = TempFile::create();
        $xliffParser->saveXML($tempFile->path, $encoding);

        $expected = \file_get_contents($expectedFilename);
        $actual = \file_get_contents($tempFile->path);

        $this->assertEquals($expected, $actual, \sprintf('testXliffExtractions: %s', $fileNameWithExtension));
        $tempFile->clean();
    }
}
