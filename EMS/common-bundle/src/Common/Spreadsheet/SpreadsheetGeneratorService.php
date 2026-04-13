<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Spreadsheet;

use EMS\CommonBundle\Common\Converter;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\DateTime;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SpreadsheetGeneratorService implements SpreadsheetGeneratorServiceInterface
{
    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>, creator: string} $config
     */
    #[\Override]
    public function generateSpreadsheetFile(array $config, string $filename, bool $normalized = false): void
    {
        $config = $this->resolveOptions($config);

        match ($config[self::WRITER]) {
            self::XLSX_WRITER => $this->getXlsxStreamedFile($config, $filename, $normalized),
            self::CSV_WRITER => $this->getCsvStreamedFile($config, $filename),
            default => throw new \RuntimeException('Unknown Spreadsheet writer'),
        };
    }

    /**
     * @param array<mixed> $config
     */
    #[\Override]
    public function generateSpreadsheet(array $config): StreamedResponse
    {
        $config = $this->resolveOptions($config);

        $response = match ($config[self::WRITER]) {
            self::XLSX_WRITER => $this->getXlsxStreamedResponse($config),
            self::CSV_WRITER => $this->getCsvStreamedResponse($config),
            default => throw new \RuntimeException('Unknown Spreadsheet writer'),
        };

        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * @param array<mixed> $config
     */
    #[\Override]
    public function generateSpreadsheetCacheableResponse(array $config): Response
    {
        $config = $this->resolveOptions($config);

        $response = match ($config[self::WRITER]) {
            self::XLSX_WRITER => $this->getXlsxResponse($config),
            self::CSV_WRITER => $this->getCsvResponse($config),
            default => throw new \RuntimeException('Unknown Spreadsheet writer'),
        };

        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * @param array<mixed> $config
     */
    private function buildUpSheets(array $config): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $cache = new Psr16Cache(new FilesystemAdapter());
        Settings::setCache($cache);

        $spreadsheet->setValueBinder(match ($config[self::VALUE_BINDER] ?? null) {
            'string' => new StringValueBinder(),
            'advanced' => new AdvancedValueBinder(),
            default => new DefaultValueBinder(),
        });

        $i = 0;
        $maxCol = 1;
        foreach ($config[self::SHEETS] as $sheetConfig) {
            $sheet = (0 === $i) ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet($i);
            $sheet->setTitle($sheetConfig['name']);
            $j = 1;

            foreach ($sheetConfig['rows'] as $row) {
                $k = 1;
                foreach ($row as $value) {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($k).$j;

                    if (\array_key_exists('validations', $sheetConfig) && null != $sheetConfig['validations'][$k - 1]) {
                        $spreadsheetValidation = $sheetConfig['validations'][$k - 1];
                        $validation = $spreadsheetValidation->addValidation($sheet->getCell($cellCoordinate)->getDataValidation());
                        $sheet->setDataValidation($cellCoordinate, $validation);
                    }

                    $this->addCell($sheet, $cellCoordinate, $this->buildCellFromValue($value));

                    ++$k;
                    $maxCol = $k > $maxCol ? $k : $maxCol;
                }
                for ($z = 1; $z <= $maxCol; ++$z) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($z))->setAutoSize(true);
                }
                ++$j;
            }
            ++$i;
        }

        if (isset($config['active_sheet'])) {
            $spreadsheet->setActiveSheetIndex($config['active_sheet']);
        }

        return $spreadsheet;
    }

    private function addCell(Worksheet $sheet, string $cellCoordinate, Cell $cell): void
    {
        $data = $cell->data;
        if ($cell->isType(Cell::TYPE_DATE) && '' !== $data && null !== $formatInput = $cell->formatInput) {
            $data = DateTime::createFromFormat($data, $formatInput)->setTime(0, 0);
            $valueBinder = new DefaultValueBinder();
        }

        if ($cell->isType(DataType::TYPE_STRING)) {
            $valueBinder = new StringValueBinder();
        }

        $value = $cell->isType(Cell::TYPE_DATE) ? Date::PHPToExcel($data) : Converter::stringify($data);
        $sheet->setCellValue($cellCoordinate, $value, $valueBinder ?? null);

        if ($cell->hasStyle()) {
            $sheet->getStyle($cellCoordinate)->applyFromArray($cell->style);
        }
        if ($cell->isType(Cell::TYPE_DATE) && null !== $formatDisplay = $cell->formatDisplay) {
            $sheet->getStyle($cellCoordinate)->getNumberFormat()->setFormatCode($formatDisplay);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function getDefaults(): array
    {
        return [
            self::CONTENT_FILENAME => 'spreadsheet',
            self::CREATOR => 'Normalized',
            self::CONTENT_DISPOSITION => 'attachment',
            self::WRITER => self::XLSX_WRITER,
            self::CSV_SEPARATOR => ',',
            self::VALUE_BINDER => null,
            'active_sheet' => 0,
        ];
    }

    /**
     * @param array<mixed> $config
     *
     * @return array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string, creator: string}
     */
    private function resolveOptions(array $config): array
    {
        $defaults = self::getDefaults();

        $resolver = new OptionsResolver();
        $resolver->setDefaults($defaults);
        $resolver->setRequired([self::WRITER, self::CONTENT_FILENAME, self::SHEETS, self::CONTENT_DISPOSITION, self::CREATOR]);
        $resolver->setAllowedTypes(self::CONTENT_DISPOSITION, ['string']);
        $resolver->setAllowedTypes(self::CREATOR, ['string']);
        $resolver->setAllowedValues(self::WRITER, [self::XLSX_WRITER, self::CSV_WRITER]);
        $resolver->setAllowedValues(self::CONTENT_DISPOSITION, ['attachment', 'inline']);
        $resolver->setAllowedValues(self::VALUE_BINDER, [null, 'string', 'advanced']);

        /** @var array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string, creator: string} $resolved */
        $resolved = $resolver->resolve($config);

        return $resolved;
    }

    private function buildCellFromValue(mixed $config): Cell
    {
        $config = \is_array($config) ? $config : [Cell::CELL_DATA => $config];

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                Cell::CELL_STYLE => [],
                Cell::CELL_TYPE => null,
                Cell::CELL_FORMAT_INPUT => null,
                Cell::CELL_FORMAT_DISPLAY => null,
            ])
            ->setRequired([Cell::CELL_DATA])
            ->setAllowedValues(Cell::CELL_TYPE, [null, 'date', DataType::TYPE_STRING])
            ->setAllowedTypes(Cell::CELL_STYLE, ['array'])
            ->setAllowedTypes(Cell::CELL_FORMAT_INPUT, ['null', 'string'])
            ->setAllowedTypes(Cell::CELL_FORMAT_DISPLAY, ['null', 'string'])
        ;

        /** @var array{data: mixed, type?: string, style: array<mixed>, format_input?: string, format_display?: string} $resolved */
        $resolved = $resolver->resolve($config);

        return new Cell(
            $resolved[Cell::CELL_DATA],
            $resolved[Cell::CELL_STYLE],
            $resolved[Cell::CELL_TYPE] ?? null,
            $resolved[Cell::CELL_FORMAT_INPUT] ?? null,
            $resolved[Cell::CELL_FORMAT_DISPLAY] ?? null,
        );
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>, creator: string} $config
     */
    private function getXlsxStreamedFile(array $config, string $filename, bool $normalized): void
    {
        $spreadsheet = $this->buildUpSheets($config);
        if ($normalized) {
            $spreadsheet->getProperties()
                ->setCreator($config[self::CREATOR])
                ->setLastModifiedBy($config[self::CREATOR])
                ->setCreated('2000-01-01 00:00:00')
                ->setModified('2000-01-01 00:00:00');
        }
        $writer = new Xlsx($spreadsheet);
        if (!$normalized) {
            $writer->save($filename);

            return;
        }

        $tempFile = TempFile::create();
        $writer->setPreCalculateFormulas(false);
        $writer->save($tempFile->path);
        $normalizer = new DeterministicXlsxNormalizer();
        $normalizer->normalize(
            $tempFile->path,
            $filename
        );
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function getXlsxStreamedResponse(array $config): StreamedResponse
    {
        $spreadsheet = $this->buildUpSheets($config);
        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $this->attachResponseHeader($response, $config, 'application/vnd.ms-excel');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function getXlsxResponse(array $config): Response
    {
        $spreadsheet = $this->buildUpSheets($config);

        $writer = new Xlsx($spreadsheet);
        $tempFile = TempFile::create();
        $writer->save($tempFile->path);
        $response = new Response($tempFile->getContents());
        $this->attachResponseHeader($response, $config, 'application/vnd.ms-excel');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string} $config
     */
    private function getCsvStreamedFile(array $config, string $filename): void
    {
        if (1 !== \count($config[self::SHEETS])) {
            throw new \RuntimeException('Exactly one sheet is expected by the CSV writer');
        }

        $handle = \fopen($filename, 'r+');
        if (false === $handle) {
            throw new \RuntimeException(\sprintf('Unexpected error while opening %s', $filename));
        }

        foreach ($config[self::SHEETS][0]['rows'] ?? [] as $row) {
            \fputcsv($handle, $row, $config[self::CSV_SEPARATOR], escape: '\\');
        }
        \fclose($handle);
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>, csv_separator: string} $config
     */
    private function getCsvStreamedResponse(array $config): StreamedResponse
    {
        if (1 !== \count($config[self::SHEETS])) {
            throw new \RuntimeException('Exactly one sheet is expected by the CSV writer');
        }

        $response = new StreamedResponse(
            function () use ($config) {
                $handle = \fopen('php://output', 'r+');
                if (false === $handle) {
                    throw new \RuntimeException('Unexpected error while opening php://output');
                }

                foreach ($config[self::SHEETS][0]['rows'] ?? [] as $row) {
                    \fputcsv($handle, $row, $config[self::CSV_SEPARATOR], escape: '\\');
                }
            }
        );
        $this->attachResponseHeader($response, $config, 'text/csv; charset=utf-8');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function getCsvResponse(array $config): Response
    {
        if (1 !== \count($config[self::SHEETS])) {
            throw new \RuntimeException('Exactly one sheet is expected by the CSV writer');
        }

        $encoders = [new CsvEncoder([CsvEncoder::NO_HEADERS_KEY => true])];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $csvContent = $serializer->serialize($config[self::SHEETS][0]['rows'], $config[self::WRITER]);

        $response = new Response($csvContent);
        $this->attachResponseHeader($response, $config, 'text/csv; charset=utf-8');

        return $response;
    }

    /**
     * @param array{writer: string, filename: string, disposition: string, sheets: array<mixed>} $config
     */
    private function attachResponseHeader(Response|StreamedResponse $response, array $config, string $type): void
    {
        $response->headers->set('Content-Type', $type);
        $response->headers->set('Content-Disposition', \sprintf('%s;filename="%s.%s"', $config[self::CONTENT_DISPOSITION], $config[self::CONTENT_FILENAME], $config[self::WRITER]));
    }
}
