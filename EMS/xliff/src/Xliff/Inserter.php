<?php

declare(strict_types=1);

namespace EMS\Xliff\Xliff;

use EMS\Xliff\XML\DomHelper;

class Inserter
{
    private readonly string $version;
    /** @var string[] */
    private array $nameSpaces = [];
    private readonly \DOMNode $xliff;
    private readonly ?string $sourceLocale;
    private readonly ?string $targetLocale;

    public function __construct(\DOMDocument $document)
    {
        $this->xliff = DomHelper::getSingleNodeFromDocument($document, 'xliff');
        $this->version = DomHelper::getStringAttr($this->xliff, 'version');

        foreach (['xml'] as $ns) {
            if ($lookupNamespace = $document->lookupNamespaceURI($ns)) {
                $this->nameSpaces[$ns] = $lookupNamespace;
            }
        }

        if (!\in_array($this->version, Extractor::XLIFF_VERSIONS)) {
            throw new \RuntimeException(\sprintf('Not supported %s XLIFF version', $this->version));
        }

        $this->sourceLocale = DomHelper::getNullStringAttr($this->xliff, 'srcLang');
        $this->targetLocale = DomHelper::getNullStringAttr($this->xliff, 'trgLang');
    }

    public static function fromFile(string $filename): Inserter
    {
        $xliff = new \DOMDocument('1.0', 'UTF-8');
        $xliff->load($filename);

        return new self($xliff);
    }

    /**
     * @return iterable<InsertionRevision>
     */
    public function getDocuments(): iterable
    {
        foreach ($this->xliff->childNodes as $document) {
            if (!$document instanceof \DOMElement) {
                continue;
            }
            yield new InsertionRevision($document, $this->version, $this->nameSpaces, $this->sourceLocale, $this->targetLocale);
        }
    }

    public function count(): int
    {
        $counter = 0;
        foreach ($this->xliff->childNodes as $document) {
            if (!$document instanceof \DOMElement) {
                continue;
            }
            ++$counter;
        }

        return $counter;
    }
}
