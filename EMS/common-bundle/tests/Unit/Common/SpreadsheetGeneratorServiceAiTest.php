<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Common;

use EMS\CommonBundle\Common\SpreadsheetGeneratorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class SpreadsheetGeneratorServiceAiTest extends TestCase
{
    private SpreadsheetGeneratorService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new SpreadsheetGeneratorService();
    }

    public function testGenerateSpreadsheetWithInvalidWriter(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "writer" with value "invalid_writer" is invalid. Accepted values are: "xlsx", "csv".');

        $config = [
            'writer' => 'invalid_writer',
            'filename' => 'test',
            'sheets' => [],
            'disposition' => 'attachment',
        ];

        $this->service->generateSpreadsheet($config);
    }

    public function testGenerateSpreadsheetXlsx(): void
    {
        $config = [
            'writer' => SpreadsheetGeneratorService::XLSX_WRITER,
            'filename' => 'test',
            'sheets' => [['name' => 'Sheet1', 'rows' => [['A1', 'B1']]]],
            'disposition' => 'attachment',
        ];

        $response = $this->service->generateSpreadsheet($config);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('application/vnd.ms-excel', $response->headers->get('Content-Type'));
    }

    public function testGenerateSpreadsheetCsv(): void
    {
        $config = [
            'writer' => SpreadsheetGeneratorService::CSV_WRITER,
            'filename' => 'test',
            'sheets' => [['name' => 'Sheet1', 'rows' => [['A1', 'B1']]]],
            'disposition' => 'attachment',
        ];

        $response = $this->service->generateSpreadsheet($config);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function testGenerateSpreadsheetCacheableResponseXlsx(): void
    {
        $config = [
            'writer' => SpreadsheetGeneratorService::XLSX_WRITER,
            'filename' => 'test',
            'sheets' => [['name' => 'Sheet1', 'rows' => [['A1', 'B1']]]],
            'disposition' => 'attachment',
        ];

        $response = $this->service->generateSpreadsheetCacheableResponse($config);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/vnd.ms-excel', $response->headers->get('Content-Type'));
    }

    public function testGenerateSpreadsheetCacheableResponseCsv(): void
    {
        $config = [
            'writer' => SpreadsheetGeneratorService::CSV_WRITER,
            'filename' => 'test',
            'sheets' => [['name' => 'Sheet1', 'rows' => [['A1', 'B1']]]],
            'disposition' => 'attachment',
        ];

        $response = $this->service->generateSpreadsheetCacheableResponse($config);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
    }
}
