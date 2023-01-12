<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Html;
use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{
    public function testStringable(): void
    {
        $this->assertEquals('test', (string) (new Html('test')));
    }

    public function testPrettyPrint(): void
    {
        $html = <<<HTML
            <div class="test">
              <h1 class="title"> 
               Title </h1>
              <hr />
              <p style="color: red"> 
              Hello  </p>
            </div>
HTML;

        $result = <<<HTML
<div class="test">
  <h1 class="title">
    Title
  </h1>
  <hr>
  <p style="color: red">
    Hello
  </p>
</div>
HTML;

        $this->assertEquals($result, (string) (new Html($html))->prettyPrint());
    }

    /**
     * @dataProvider getDataHtmlSanitize
     */
    public function testSanitize(string $html, string $expected, array $sanitize): void
    {
        $this->assertEquals($expected, (string) (new Html($html))->sanitize($sanitize));
    }

    private function getDataHtmlSanitize(): array
    {
        return [
            'testSafeElements' => [
                '<div>div test</div><span>span test</span>',
                '<div>div test</div><span>span test</span>',
                ['allow_safe_elements' => true],
            ],
            'testAllowAttributes' => [
                '<div class="test">div test</div><span class="test">span test</span>',
                '<div class="test">div test</div><span class="test">span test</span>',
                ['allow_attributes' => [['name' => 'class', 'elements' => '*']]],
            ],
            'testAllowElements' => [
                '<div>div test</div><span>span test</span>',
                '<div>div test</div>',
                ['allow_safe_elements' => false, 'allow_elements' => [['name' => 'div', 'attributes' => '*']]],
            ],
            'testBlockElements' => [
                '<div>div test</div><span>span test</span>',
                '<div>div test</div>span test',
                ['block_elements' => ['span']],
            ],
            'testDropElements' => [
                '<div>div test</div><span>span test</span>',
                '<div>div test</div>',
                ['drop_elements' => ['span']],
            ],
        ];
    }
}
