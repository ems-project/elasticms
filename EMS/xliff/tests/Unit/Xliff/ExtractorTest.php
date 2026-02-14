<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\File\TempFile;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\Xliff;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $options = new Options(Version::V12);
        $xliffPackage = Xliff::create($options);
        $xliffPackage->init('nl', 'de');
        $package = $xliffPackage->getPackage();
        $document = $package->addDocument('contentType:ouuid_1:revisionId_1');
        $document->createText('[title]', 'titre', 'titre', 'titre');
        $document->createHtml('[%locale%][body]', $rawData['nl']['body'], $existing['de']['body']);

        $readerPackage = Xliff::create($options);
        $readerPackage->readXml($xliffPackage->toXml());
        foreach ($readerPackage->getPackage()->getDocuments() as $document) {
            $document->unitToAssociativeArray($readerPackage->getPackage(), $rawData, $extracted);
        }
        $this->assertSame(1, $readerPackage->getPackage()->getInsertReport()->countErrors());
        $error = $readerPackage->getPackage()->getInsertReport()->getErrors()['revisionId_1'][0];
        $this->assertSame('titre', $error->getReceived());
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
            'title' => 'titre',
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

            foreach (Version::ALL as $version) {
                $options = new Options($version);
                $xliffPackage = Xliff::create($options);
                $xliffPackage->init('en', 'fr');
                $package = $xliffPackage->getPackage();
                $document = $package->addDocument('contentType:ouuid_1:revisionId_1');
                $document->createText('[title_%locale%]', 'Foo', 'Bar');
                $document = $package->addDocument('contentType:ouuid_2:revisionId_2');
                $document->createText('[title_%locale%]', 'Hello', 'Bonjour');
                $document->createText('[keywords_%locale%]', 'test xliff');
                $document->createText('[empty]', '', isFinal: true);
                $document->createHtml('[%locale%][body]', $htmlSource, $htmlTarget ?: null);
                $document->createHtml('[%locale%][body2]', $htmlSource, $htmlTarget ?: null, null, true);

                $this->saveAndCompare($absoluteFilePath, $version, $xliffPackage, $fileNameWithExtension, 'UTF-8');
                $this->saveAndCompare($absoluteFilePath, $version, $xliffPackage, $fileNameWithExtension, 'us-ascii');
            }
        }
    }

    public function saveAndCompare(string $absoluteFilePath, string $version, Xliff $xliffPackage, string $fileNameWithExtension, string $encoding): void
    {
        $expectedFilename = $absoluteFilePath.DIRECTORY_SEPARATOR.'expected_'.$encoding.$version.'.xlf';
        if (!\file_exists($expectedFilename)) {
            $xliffPackage->saveXml($expectedFilename, encoding: $encoding);
        }

        $tempFile = TempFile::create();
        $xliffPackage->saveXML($tempFile->path, encoding: $encoding);

        $expected = \file_get_contents($expectedFilename);
        $actual = \file_get_contents($tempFile->path);

        $this->assertEquals($expected, $actual, \sprintf('testXliffExtractions: %s', $fileNameWithExtension));
        $tempFile->clean();
    }

    /**
     * @return array<array<int|string>>
     */
    public static function withBaselineProvider(): array
    {
        return [[
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'WithBaseline', 'TC-1', 'source.html'])),
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'WithBaseline', 'TC-1', 'target.html'])),
            \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'WithBaseline', 'TC-1', 'baseline.html'])),
            \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'WithBaseline', 'TC-1', 'extracted.xlf']),
        ]];
    }

    #[DataProvider('withBaselineProvider')]
    public function testWithBaseline(string $sourceHtml, string $targetHtml, string $baselineHtml, string $expectedPath): void
    {
        $options = new Options(Version::V12);
        $xliffPackage = Xliff::create($options);
        $xliffPackage->init('nl', 'de');
        $package = $xliffPackage->getPackage();
        $document = $package->addDocument('content_type:fakeOuuid:fakeRevisionId');
        $document->createHtml('[body]', $sourceHtml, $targetHtml, $baselineHtml);

        if (!\file_exists($expectedPath)) {
            $xliffPackage->saveXML($expectedPath);
        }
        $expected = \file_get_contents($expectedPath);

        $tempFile = TempFile::create();
        $xliffPackage->saveXML($tempFile->path);
        $extracted = \file_get_contents($tempFile->path);
        $this->assertSame($expected, $extracted);
        $tempFile->clean();
    }
}
