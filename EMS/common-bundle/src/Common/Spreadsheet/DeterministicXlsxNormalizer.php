<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Spreadsheet;

use EMS\Helpers\File\TempDirectory;

final readonly class DeterministicXlsxNormalizer
{
    private string $fixedIso8601;
    private int $fixedUnixTime;

    public function __construct(
        ?\DateTimeImmutable $fixedDate = null,
        private string $fixedCreator = 'Normalized',
        private string $fixedLastModifiedBy = 'Normalized',
        private string $fixedCalcId = '171027'
    ) {
        $fixedDate ??= new \DateTimeImmutable('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $utcDate = $fixedDate->setTimezone(new \DateTimeZone('UTC'));

        $this->fixedIso8601 = $utcDate->format('Y-m-d\TH:i:s\Z');
        $this->fixedUnixTime = $utcDate->getTimestamp();
    }

    public function normalize(string $inputFile, string $outputFile): void
    {
        $this->assertEnvironment($inputFile);

        $tempDirectory = TempDirectory::create();
        $extractDir = $tempDirectory->path;

        try {
            $this->extractZip($inputFile, $extractDir);

            $this->normalizeCoreXml($extractDir.'/docProps/core.xml');
            $this->normalizeAppXml($extractDir.'/docProps/app.xml');
            $this->normalizeWorkbookXml($extractDir.'/xl/workbook.xml');
            $this->removeIfExists($extractDir.'/xl/calcChain.xml');
            $this->normalizeContentTypes($extractDir.'/[Content_Types].xml');

            $this->createDeterministicZip($extractDir, $outputFile);
        } catch (\Throwable $throwable) {
            throw new \RuntimeException(\sprintf('Impossible to normalize the file XLSX "%s": %s', $inputFile, $throwable->getMessage()), 0, $throwable);
        }
    }

    private function assertEnvironment(string $inputFile): void
    {
        if (!\is_file($inputFile)) {
            throw new \RuntimeException(\sprintf('File not found: %s', $inputFile));
        }
    }

    private function extractZip(string $zipFile, string $destination): void
    {
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFile)) {
            throw new \RuntimeException(\sprintf('Unable to open the ZIP/XLSX: %s', $zipFile));
        }

        if (!$zip->extractTo($destination)) {
            $zip->close();
            throw new \RuntimeException(\sprintf('Unable to extract the ZIP/XLSX: %s', $zipFile));
        }

        $zip->close();
    }

    private function normalizeCoreXml(string $file): void
    {
        if (!\is_file($file)) {
            return;
        }

        $dom = $this->loadXml($file);
        $xpath = new \DOMXPath($dom);

        $xpath->registerNamespace('cp', 'http://schemas.openxmlformats.org/package/2006/metadata/core-properties');
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $xpath->registerNamespace('dcterms', 'http://purl.org/dc/terms/');

        $this->setSingleNodeValue($xpath, '/cp:coreProperties/dc:creator', $this->fixedCreator);
        $this->setSingleNodeValue($xpath, '/cp:coreProperties/cp:lastModifiedBy', $this->fixedLastModifiedBy);

        $created = $this->ensureSingleNode(
            $dom,
            $xpath,
            '/cp:coreProperties/dcterms:created',
            'created',
            'http://purl.org/dc/terms/',
            '/cp:coreProperties'
        );
        $created->nodeValue = $this->fixedIso8601;
        $created->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:type',
            'dcterms:W3CDTF'
        );

        $modified = $this->ensureSingleNode(
            $dom,
            $xpath,
            '/cp:coreProperties/dcterms:modified',
            'modified',
            'http://purl.org/dc/terms/',
            '/cp:coreProperties'
        );
        $modified->nodeValue = $this->fixedIso8601;
        $modified->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:type',
            'dcterms:W3CDTF'
        );

        $this->saveXml($dom, $file);
    }

    private function normalizeAppXml(string $file): void
    {
        if (!\is_file($file)) {
            return;
        }

        $dom = $this->loadXml($file);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ep', 'http://schemas.openxmlformats.org/officeDocument/2006/extended-properties');

        $this->setSingleNodeValue($xpath, '/ep:Properties/ep:Manager', '');
        $this->setSingleNodeValue($xpath, '/ep:Properties/ep:Company', '');
        $this->setSingleNodeValue($xpath, '/ep:Properties/ep:AppVersion', '1.0');

        $this->saveXml($dom, $file);
    }

    private function normalizeWorkbookXml(string $file): void
    {
        if (!\is_file($file)) {
            return;
        }

        $dom = $this->loadXml($file);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $calcPr = $this->query($xpath, '/x:workbook/x:calcPr')->item(0);

        if (!$calcPr instanceof \DOMElement) {
            $workbook = $this->query($xpath, '/x:workbook')->item(0);
            if ($workbook instanceof \DOMElement) {
                $calcPr = $dom->createElementNS(
                    'http://schemas.openxmlformats.org/spreadsheetml/2006/main',
                    'calcPr'
                );
                $workbook->appendChild($calcPr);
            }
        }

        if ($calcPr instanceof \DOMElement) {
            $calcPr->setAttribute('calcId', $this->fixedCalcId);
            $calcPr->setAttribute('calcMode', 'auto');
            $calcPr->setAttribute('fullCalcOnLoad', '0');
            $calcPr->setAttribute('forceFullCalc', '0');
        }

        $this->saveXml($dom, $file);
    }

    private function normalizeContentTypes(string $file): void
    {
        if (!\is_file($file)) {
            return;
        }

        $dom = $this->loadXml($file);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ct', 'http://schemas.openxmlformats.org/package/2006/content-types');

        $nodes = $xpath->query('/ct:Types/ct:Override[@PartName="/xl/calcChain.xml"]');
        if (false !== $nodes) {
            foreach ($nodes as $node) {
                if (!$node instanceof \DOMNode) {
                    continue;
                }
                $node->parentNode?->removeChild($node);
            }
        }

        $this->saveXml($dom, $file);
    }

    private function createDeterministicZip(string $sourceDir, string $outputFile): void
    {
        $files = $this->listFilesRecursively($sourceDir);
        \sort($files, SORT_STRING);

        $zip = new \ZipArchive();
        if (true !== $zip->open($outputFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \RuntimeException(\sprintf('Unable to created the output file %s', $outputFile));
        }

        foreach ($files as $absolutePath) {
            $localName = \str_replace('\\', '/', \substr($absolutePath, \strlen($sourceDir) + 1));
            $contents = \file_get_contents($absolutePath);

            if (false === $contents) {
                $zip->close();
                throw new \RuntimeException(\sprintf('Unable to read the file: %s', $absolutePath));
            }

            if (!$zip->addFromString($localName, $contents)) {
                $zip->close();
                throw new \RuntimeException(\sprintf('Unable to add the ZIP: %s', $localName));
            }

            $zip->setMtimeName($localName, $this->fixedUnixTime);
        }

        $zip->close();
    }

    /**
     * @return list<string>
     */
    private function listFilesRecursively(string $dir): array
    {
        $result = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var \SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $result[] = $fileInfo->getPathname();
            }
        }

        return $result;
    }

    private function loadXml(string $file): \DOMDocument
    {
        $xml = \file_get_contents($file);
        if (false === $xml) {
            throw new \RuntimeException(\sprintf('Unable to read the XML: %s', $file));
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        \libxml_use_internal_errors(true);
        $ok = $dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);

        if (!$ok) {
            $errors = \libxml_get_errors();
            \libxml_clear_errors();
            $message = isset($errors[0]) ? \trim($errors[0]->message) : 'Invalid XML';
            throw new \RuntimeException(\sprintf('Impossible to parse the XML "%s": %s', $file, $message));
        }

        return $dom;
    }

    private function saveXml(\DOMDocument $dom, string $file): void
    {
        $xml = $dom->saveXML();
        if (false === $xml) {
            throw new \RuntimeException(\sprintf('Impossible to serialize the XML: %s', $file));
        }

        if (false === \file_put_contents($file, $xml)) {
            throw new \RuntimeException(\sprintf('Impossible to write the XML: %s', $file));
        }
    }

    private function setSingleNodeValue(\DOMXPath $xpath, string $query, string $value): void
    {
        $node = $this->query($xpath, $query)->item(0);
        if ($node instanceof \DOMNode) {
            $node->nodeValue = $value;
        }
    }

    private function ensureSingleNode(
        \DOMDocument $dom,
        \DOMXPath $xpath,
        string $query,
        string $localName,
        string $namespace,
        string $parentQuery
    ): \DOMElement {
        $node = $this->query($xpath, $query)->item(0);
        if ($node instanceof \DOMElement) {
            return $node;
        }

        $parent = $this->query($xpath, $parentQuery)->item(0);
        if (!$parent instanceof \DOMElement) {
            throw new \RuntimeException(\sprintf('Unfindable parent XML for %s', $query));
        }

        $newNode = $dom->createElementNS($namespace, $localName);
        $parent->appendChild($newNode);

        return $newNode;
    }

    private function removeIfExists(string $file): void
    {
        if (\is_file($file) && !\unlink($file)) {
            throw new \RuntimeException(\sprintf('Impossible to delete the file: %s', $file));
        }
    }

    /**
     * @return \DOMNodeList<\DOMNameSpaceNode|\DOMNode>
     */
    private function query(\DOMXPath $xpath, string $query): \DOMNodeList
    {
        $result = $xpath->query($query);
        if (!$result) {
            throw new \RuntimeException(\sprintf('Unreadable field %s', $query));
        }

        return $result;
    }
}
