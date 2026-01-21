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
            $xliffPackage = Xliff::createDefault($options);
            $this->assertSame($xliffPackage->getOptions()->defaultVersion, $version);

            // TODO: $xDocument = $xliffPackage->addDocument('page:document_id:revision_id');
            // TODO: $xDocument->addText('[title]', 'title', 'titre', 'title');
            // TODO: $xDocument->addHtml('[body]', '<p>body</p>', '<p>corp</p>', '<p>body</p>');

            $xml = $xliffPackage->toXml();
            $this->assertNotEmpty($xml);

            $xliffPackage = Xliff::createDefault();
            $xliffPackage->readXml($xml);
            $this->assertEmpty($xliffPackage->getPackage()->getDocuments());
        }
    }
}
