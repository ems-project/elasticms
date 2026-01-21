<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\Xliff;
use PHPUnit\Framework\TestCase;

class RefactorTest extends TestCase
{
    public function testEmptyPackage(): void
    {
        foreach (Version::ALL as $version) {
            $options = new Options($version);
            $xliffPackage = Xliff::createDefault('en', 'fr', $options);
            $this->assertSame($xliffPackage->getOptions()->defaultVersion, $version);

            // TODO: $xDocument = $xliffPackage->addDocument('page:document_id:revision_id');
            // TODO: $xDocument->addText('[title]', 'title', 'titre', 'title');
            // TODO: $xDocument->addHtml('[body]', '<p>body</p>', '<p>corp</p>', '<p>body</p>');

            $xml = $xliffPackage->toXml();
            $this->assertNotEmpty($xml);

            $xliffPackage = Xliff::createDefault('en', 'fr');
            $xliffPackage->readXml($xml);
            $this->assertEmpty($xliffPackage->getPackage()->getDocuments());
        }
    }

    public function testWithEmptyDocuments(): void
    {
        foreach (Version::ALL as $version) {
            $options = new Options($version);
            $xliffPackage = Xliff::createDefault('fr_FR', 'fr_BE', $options);
            $this->assertSame($xliffPackage->getOptions()->defaultVersion, $version);

            $xliffPackage->getPackage()->addDocument('1');
            $xliffPackage->getPackage()->addDocument('2');

            $xml = $xliffPackage->toXml();
            $this->assertNotEmpty($xml);

            $xliffPackage = Xliff::createDefault('fr_FR', 'fr_BE');
            $xliffPackage->readXml($xml);
            $this->assertCount(2, $xliffPackage->getPackage()->getDocuments());
            $i = 1;
            foreach ($xliffPackage->getPackage()->getDocuments() as $document) {
                $this->assertSame(\sprintf('%d', $i++), $document->id);
            }
        }
    }
}
