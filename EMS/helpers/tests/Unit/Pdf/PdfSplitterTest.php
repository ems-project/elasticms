<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Pdf;

use EMS\Helpers\Pdf\PdfSplitter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfSplitterTest extends TestCase
{
    public function testExtractFirstPages(): void
    {
        $splitter = new PdfSplitter(__DIR__.'/fixtures/11 pages.pdf');
        $filename = $splitter->extractFirstPages(5);
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($filename);
        $this->assertEquals(5, $pageCount);
    }

    #[DataProvider('getDataSplit')]
    public function testSplit(string $filename, int $from, ?int $to, $expected): void
    {
        $splitter = new PdfSplitter($filename);
        $filename = $splitter->split($from, $to);
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($filename);
        $this->assertEquals($expected, $pageCount);
    }

    public static function getDataSplit(): array
    {
        return [
            'all' => [
                __DIR__.'/fixtures/11 pages.pdf',
                1,
                null,
                11,
            ],
            'all11' => [
                __DIR__.'/fixtures/11 pages.pdf',
                1,
                11,
                11,
            ],
            'skipFirstPage' => [
                __DIR__.'/fixtures/11 pages.pdf',
                2,
                null,
                10,
            ],
            'skipFirstPageWithToMuch' => [
                __DIR__.'/fixtures/11 pages.pdf',
                2,
                1000,
                10,
            ],
            'lastPage' => [
                __DIR__.'/fixtures/11 pages.pdf',
                11,
                null,
                1,
            ],
            'from5' => [
                __DIR__.'/fixtures/11 pages.pdf',
                5,
                null,
                7,
            ],
            '3from5' => [
                __DIR__.'/fixtures/11 pages.pdf',
                5,
                3,
                3,
            ],
            'max7Pages' => [
                __DIR__.'/fixtures/11 pages.pdf',
                1,
                7,
                7,
            ],
            'max1000Pages' => [
                __DIR__.'/fixtures/11 pages.pdf',
                1,
                1000,
                11,
            ],
        ];
    }
}
