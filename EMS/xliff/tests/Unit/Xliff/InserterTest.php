<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Helpers\Html\HtmlHelper;
use EMS\Helpers\Standard\Json;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\Xliff;
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

        $formated = HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($document->saveXML($body)));
        $this->assertEquals('<a href=
"%{BASE_URL_CURRENT}%/instructions/persons/specific/childsitter.html">
Click here</a>', $formated);
    }

    public function testXliffImports(): void
    {
        $finder = new Finder();
        $finder->in(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Imports']))->directories();

        foreach ($finder as $file) {
            $absoluteFilePath = $file->getRealPath();
            $fileNameWithExtension = $file->getRelativePathname();

            $xliff = Xliff::create(new Options(Version::V12));
            $xliff->fromFile($absoluteFilePath.DIRECTORY_SEPARATOR.'translated.xlf');
            foreach ($xliff->getPackage()->getDocuments() as $document) {
                [$contentType, $ouuid, $revisionId] = \explode(':', $document->id);
                $corresponding = \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Revisions', $contentType, $ouuid, $revisionId.'.json']));
                $this->assertNotFalse($corresponding);
                $correspondingJson = Json::decode($corresponding);
                $this->assertIsArray($correspondingJson);
                $target = [];
                $document->unitToAssociativeArray($xliff->getPackage(), $correspondingJson, $target);

                $expectedFilename = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'Translated', $contentType.'-'.$ouuid.'-'.$revisionId.'.json']);
                if (!\file_exists($expectedFilename)) {
                    \file_put_contents($expectedFilename, Json::encode($target, true));
                }
                $expected = Json::decode(\file_get_contents($expectedFilename));
                $this->assertEquals($expected, $target, \sprintf('For the document ems://%s:%s revision %s during the test %s', $contentType, $ouuid, $revisionId, $fileNameWithExtension));
            }
        }
    }
}
