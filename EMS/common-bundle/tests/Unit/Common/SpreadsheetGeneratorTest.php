<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common;

use EMS\CommonBundle\Common\Spreadsheet\SpreadsheetGeneratorService;
use EMS\CommonBundle\Common\Spreadsheet\SpreadsheetValidation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpreadsheetGeneratorTest extends TestCase
{
    private SpreadsheetGeneratorService $spreadSheetGenerator;

    #[\Override]
    protected function setUp(): void
    {
        $this->spreadSheetGenerator = new SpreadsheetGeneratorService();
        parent::setUp();
    }

    public function testConfigToExcel(): void
    {
        $config = \json_decode('{"filename":"export","writer":"xlsx","active_sheet":0,"sheets":[{"name":"Export form","color":"#FF0000","rows":[["apple","banana"],["pineapple","strawberry"]]},{"name":"Export form sheet 2","rows":[["a1","a2"],["b1","b3"]]}]}', true);
        $this->assertSame('Export form', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$config])->getActiveSheet()->getTitle());
        $this->assertSame('pineapple', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$config])->getActiveSheet()->getCell('A2')->getValue());

        $configColor = \json_decode('{"filename":"export_with_color","writer":"xlsx","active_sheet":0,"sheets":[{"name":"Export form with Color","color":"#FF0000","rows":[[{"data":"apple"},{"data":"banana","style":{"fill":{"fillType":"solid","color":{"rgb":"F9D73F"}}}}],[{"data":"pineapple","style":{"fill":{"fillType":"solid","color":{"rgb":"F9D73F"}}}},{"data":"strawberry","style":{}}]]}]}', true);
        $this->assertSame('Export form with Color', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$configColor])->getActiveSheet()->getTitle());
        $this->assertSame('pineapple', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$configColor])->getActiveSheet()->getCell('A2')->getValue());
    }

    public function testConfigWithValidationToExcel(): void
    {
        $validation = new SpreadsheetValidation([
            'type' => 'list',
            'formula' => 'low,medium,high',
            'allow_blank' => true,
            'prompt_title' => 'Chose a level',
            'error_title' => 'Level wrong',
            'show_input' => true,
            'show_error' => true,
        ]);

        $config = \json_decode('{"filename":"export","writer":"xlsx","active_sheet":0,"sheets":[{"name":"Export form","color":"#FF0000","rows":[["apple","low"],["pineapple",""]]},{"name":"Export form sheet 2","rows":[["a1",""],["b2",""]]}]}', true);
        foreach ($config['sheets'] as $index => $sheet) {
            $config['sheets'][$index] = \array_merge($sheet, ['validations' => [null, $validation]]);
        }
        $this->assertSame('Export form', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$config])->getActiveSheet()->getTitle());
        $this->assertSame('pineapple', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$config])->getActiveSheet()->getCell('A2')->getValue());

        $configColor = \json_decode('{"filename":"export_with_color","writer":"xlsx","active_sheet":0,"sheets":[{"name":"Export form with Color","color":"#FF0000","rows":[[{"data":"apple"},{"data":"low","style":{"fill":{"fillType":"solid","color":{"rgb":"F9D73F"}}}}],[{"data":"pineapple","style":{"fill":{"fillType":"solid","color":{"rgb":"F9D73F"}}}},{"data":"","style":{}}]]}]}', true);
        foreach ($configColor['sheets'] as $index => $sheet) {
            $configColor['sheets'][$index] = \array_merge($sheet, ['validations' => [null, $validation]]);
        }
        $this->assertSame('Export form with Color', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$configColor])->getActiveSheet()->getTitle());
        $this->assertSame('pineapple', $this->callMethod($this->spreadSheetGenerator, 'buildUpSheets', [$configColor])->getActiveSheet()->getCell('A2')->getValue());
    }

    public function testConfigToCsv(): void
    {
        $config = \json_decode('{"filename":"test-export","writer":"csv","disposition":"inline","sheets":[{"rows":[["apple","banana"],["pineapple","strawberry"],["àï$@,& & \\" \' ! @ # $ €","foobar"]]}]}', true);
        /** @var StreamedResponse $csv */
        $csv = $this->callMethod($this->spreadSheetGenerator, 'getCsvResponse', [$config]);

        // https://github.com/symfony/symfony/issues/25005
        \ob_start();
        $csv->send();
        $getContent = \ob_get_contents();
        \ob_end_clean();

        $lines = \preg_split('/\r\n|\r|\n/', $getContent);
        $this->assertCount(4, $lines);
        $this->assertSame('apple,banana', $lines[0]);
        $this->assertSame('pineapple,strawberry', $lines[1]);
        $this->assertSame('"àï$@,& & "" \' ! @ # $ €",foobar', $lines[2]);
        $this->assertSame('', $lines[3]);
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    private function callMethod($object, string $method, array $parameters = [])
    {
        try {
            $className = $object::class;
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \Exception($e->getMessage());
        }

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
