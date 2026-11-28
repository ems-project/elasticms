<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Image;

use EMS\Helpers\File\TempFile;
use EMS\Helpers\Image\WebpXmpWriter;
use EMS\Helpers\Image\XmpMetadata;
use PHPUnit\Framework\TestCase;

class WebpXmpWriterTest extends TestCase
{
    public const string XML_SAMPLE = <<<XML
        <?xpacket begin="" id="W5M0MpCehiHzreSzNTczkc9d"?>
        <x:xmpmeta xmlns:x="adobe:ns:meta/">
          <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
            <rdf:Description
              rdf:about=""
              xmlns:dc="http://purl.org/dc/elements/1.1/">
              <dc:creator>
                <rdf:Seq>
                  <rdf:li>elasticMS</rdf:li>
                </rdf:Seq>
              </dc:creator>
              <dc:rights>
                <rdf:Alt>
                  <rdf:li xml:lang="x-default">© elasticMS 2026</rdf:li>
                </rdf:Alt>
              </dc:rights>
            </rdf:Description>
          </rdf:RDF>
        </x:xmpmeta>
        <?xpacket end="w"?>
        XML;

    public function testWithXmpMetadata(): void
    {
        $metadata = new XmpMetadata(
            author: 'elasticMS',
            copyright: '© elasticMS 2026',
        );
        $xmp = $metadata->toXmp();
        $this->assertSame(self::XML_SAMPLE, $xmp);

        $webpXmpWriter = new WebpXmpWriter();
        $tempFile = TempFile::create();
        $filename = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'Resources', 'WebpXmlWriter', 'avatar.webp']);
        $webpXmpWriter->writeFile($filename, $tempFile->path, $xmp);

        $taggedImage = \imagecreatefromwebp($tempFile->path);
        $this->assertNotFalse($taggedImage);
        $imageSize = \getimagesize($tempFile->path);
        $this->assertSame(400, $imageSize['0']);
        $this->assertSame(400, $imageSize['1']);
        $this->assertSame($xmp, $webpXmpWriter->extractXmp($tempFile->getContents()));
    }
}
