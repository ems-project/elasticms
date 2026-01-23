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
            $xliffPackage = Xliff::create($options);
            $xliffPackage->init('en', 'fr');
            $this->assertSame($xliffPackage->getOptions()->defaultVersion, $version);
            $xml = $xliffPackage->toXml();
            $this->assertNotEmpty($xml);

            $xliffPackage = Xliff::create();
            $xliffPackage->readXml($xml);
            $this->assertEmpty($xliffPackage->getPackage()->getDocuments());
        }
    }

    public function testWithEmptyDocuments(): void
    {
        foreach (Version::ALL as $version) {
            $options = new Options($version);
            $xliffPackage = Xliff::create($options);
            $xliffPackage->init('fr_FR', 'fr_BE');
            $this->assertSame($xliffPackage->getOptions()->defaultVersion, $version);

            $xliffPackage->getPackage()->addDocument('1');
            $xliffPackage->getPackage()->addDocument('2');

            $xml = $xliffPackage->toXml();
            $this->assertNotEmpty($xml);

            $xliffPackage = Xliff::create();
            $xliffPackage->readXml($xml);
            $this->assertCount(2, $xliffPackage->getPackage()->getDocuments());
            $this->assertSame('fr-FR', $xliffPackage->getPackage()->getSourceLocale());
            $this->assertSame('fr-BE', $xliffPackage->getPackage()->getTargetLocale());
            $i = 1;
            foreach ($xliffPackage->getPackage()->getDocuments() as $document) {
                $this->assertSame(\sprintf('%d', $i++), $document->id);
            }
        }
    }
}
