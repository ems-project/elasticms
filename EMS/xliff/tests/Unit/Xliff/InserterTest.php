<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\Standard\Json;
use EMS\Xliff\Xliff\Entity\InsertReport;
use EMS\Xliff\Xliff\Inserter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class InserterTest extends TestCase
{
    public function testAttrWithCurlBracket(): void
    {
        $document = new \DOMDocument();
        $html = new \DOMElement('html');
        $document->appendChild($html);
        $body = new \DOMElement('body');
        $html->appendChild($body);
        $link = new \DOMElement('a', 'Click here');
        $supp = new \DOMElement('supp');
        $body->appendChild($link);
        $body->appendChild(new \DOMText(' '));
        $body->appendChild($supp);
        $link->setAttribute('href', '%{BASE_URL_CURRENT}%/instructions/persons/specific/childsitter.html');

        $formater = new \tidy();
        $formater->parseString($document->saveXML($body), [
            'newline' => 'LF',
        ]);
        $this->assertEquals('<body>
<a href=
"%{BASE_URL_CURRENT}%/instructions/persons/specific/childsitter.html">
Click here</a>
</body>
', $formater->body()->value);
    }

    public function testXliffImports(): void
    {
        $finder = new Finder();
        $finder->in(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Imports']))->directories();
        $insertReport = new InsertReport();

        foreach ($finder as $file) {
            $absoluteFilePath = $file->getRealPath();
            $fileNameWithExtension = $file->getRelativePathname();

            $importer = Inserter::fromFile($absoluteFilePath.DIRECTORY_SEPARATOR.'translated.xlf');
            foreach ($importer->getDocuments() as $document) {
                $corresponding = \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Revisions', $document->getContentType(), $document->getOuuid(), $document->getRevisionId().'.json']));
                $this->assertNotFalse($corresponding);
                $correspondingJson = Json::decode($corresponding);
                $this->assertIsArray($correspondingJson);
                $target = [];
                $document->extractTranslations($insertReport, $correspondingJson, $target);

                $expectedFilename = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Translated', $document->getContentType().'-'.$document->getOuuid().'-'.$document->getRevisionId().'.json']);
                if (!\file_exists($expectedFilename)) {
                    \file_put_contents($expectedFilename, Json::encode($target, true));
                }
                $expected = Json::decode(\file_get_contents($expectedFilename));
                $this->assertEquals($expected, $target, \sprintf('For the document ems://%s:%s revision %s during the test %s', $document->getContentType(), $document->getOuuid(), $document->getRevisionId(), $fileNameWithExtension));
            }
        }
    }
}
