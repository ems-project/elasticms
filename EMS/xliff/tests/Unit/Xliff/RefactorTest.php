<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Xliff\Model\Inline\Text;
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

    public function testWithDocumentsWithText(): void
    {
        foreach (Version::ALL as $version) {
            $options = new Options($version);
            $xliffPackage = Xliff::create($options);
            $xliffPackage->init('fr_FR', 'fr_BE');
            $this->assertSame($xliffPackage->getOptions()->defaultVersion, $version);

            $document = $xliffPackage->getPackage()->addDocument('1');
            $document->createText('[title]', 'title1', 'titre1', 'title1');
            $document = $xliffPackage->getPackage()->addDocument('2');
            $document->createText('[title]', 'title2', 'titre2', 'title');
            $document = $xliffPackage->getPackage()->addDocument('3');
            $document->createText('[title]', 'title3', 'titre3');
            $document = $xliffPackage->getPackage()->addDocument('4');
            $document->createText('[title]', 'title4');

            $xml = $xliffPackage->toXml();
            $this->assertNotEmpty($xml);
            $xliffPackage = Xliff::create();
            $xliffPackage->readXml($xml);
            $this->assertCount(4, $xliffPackage->getPackage()->getDocuments());
            $this->assertSame('fr-FR', $xliffPackage->getPackage()->getSourceLocale());
            $this->assertSame('fr-BE', $xliffPackage->getPackage()->getTargetLocale());
            $i = 0;
            foreach ($xliffPackage->getPackage()->getDocuments() as $document) {
                $this->assertSame(\sprintf('%d', ++$i), $document->id);
                $this->assertCount(1, $document->getNodes());
                foreach ($document->getNodes() as $unit) {
                    $this->assertSame('[title]', $unit->resourceName);
                    $this->assertSame(null, $unit->type);
                    $this->assertCount(1, $unit->getSegments());
                    foreach ($unit->getSegments() as $segment) {
                        $this->assertCount(1, $segment->getSourceNodes());
                        $this->assertInstanceOf(Text::class, $segment->getSourceNodes()[0]);
                        $this->assertSame(\sprintf('title%d', $i), $segment->getSourceNodes()[0]->text);
                        if (empty($segment->getTargetNodes())) {
                            $this->assertSame(4, $i);
                            $this->assertSame(Xliff::STATE_NEW, $segment->getState());
                        } else {
                            $this->assertCount(1, $segment->getTargetNodes());
                            $this->assertSame(\sprintf('titre%d', $i), $segment->getTargetNodes()[0]->text);
                            match (''.$i) {
                                '1' => $this->assertSame(Xliff::STATE_FINAL, $segment->getState()),
                                '2', '3' => $this->assertSame(Xliff::STATE_NEEDS_TRANSLATION, $segment->getState()),
                            };
                        }
                    }
                }
            }
        }
    }
}
