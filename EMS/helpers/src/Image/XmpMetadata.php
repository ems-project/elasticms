<?php

declare(strict_types=1);

namespace EMS\Helpers\Image;

final readonly class XmpMetadata
{
    public function __construct(
        private ?string $author = null,
        private ?string $copyright = null,
    ) {
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function toXmp(): string
    {
        $author = $this->escapeXml($this->author);
        $copyright = $this->escapeXml($this->copyright);

        $parts = [];

        if (null !== $author && '' !== $author) {
            $parts[] = <<<XML
                      <dc:creator>
                        <rdf:Seq>
                          <rdf:li>{$author}</rdf:li>
                        </rdf:Seq>
                      </dc:creator>
                XML;
        }

        if (null !== $copyright && '' !== $copyright) {
            $parts[] = <<<XML
                      <dc:rights>
                        <rdf:Alt>
                          <rdf:li xml:lang="x-default">{$copyright}</rdf:li>
                        </rdf:Alt>
                      </dc:rights>
                XML;
        }

        $descriptionContent = \implode("\n", $parts);

        return <<<XML
            <?xpacket begin="" id="W5M0MpCehiHzreSzNTczkc9d"?>
            <x:xmpmeta xmlns:x="adobe:ns:meta/">
              <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
                <rdf:Description
                  rdf:about=""
                  xmlns:dc="http://purl.org/dc/elements/1.1/">
            {$descriptionContent}
                </rdf:Description>
              </rdf:RDF>
            </x:xmpmeta>
            <?xpacket end="w"?>
            XML;
    }

    private function escapeXml(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return \htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
