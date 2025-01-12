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
            'testMaxInputLength' => [
                '<p>12345</p>',
                '<p>Input length (<strong>12</strong>) exceeded max input length (<strong>5</strong>)</p>',
                ['allow_safe_elements' => true, 'max_input_length' => 5],
            ],
            'testEMSLinks' => [
                '<a href="ems://object:page:876eb204-c3a3-43a6-94b6-9124a7206b1b">test</a>',
                '<a href="ems://object:page:876eb204-c3a3-43a6-94b6-9124a7206b1b">test</a>',
                ['allow_safe_elements' => true],
            ],
            'testAllowLinks' => [
                '<a href="http://www.example.com">http test</a><a href="https://www.example.com">http test</a><a href="mailto:support@example.com">mail test</a>',
                '<a href="http://www.example.com">http test</a><a href="https://www.example.com">http test</a><a href="mailto:support&#64;example.com">mail test</a>',
                ['allow_safe_elements' => true],
            ],
            'testInlineData' => [
                '<img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD" />',
                '<img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD" />',
                ['allow_safe_elements' => true],
            ],
            'testSafeElements' => [
                '<div>div test</div><span>span test</span>',
                '<div>div test</div><span>span test</span>',
                ['allow_safe_elements' => true],
            ],
            'testAllowAttributes' => [
                '<div data-test="test">div test</div><span data-test="test">span test</span>',
                '<div data-test="test">div test</div><span data-test="test">span test</span>',
                ['allow_attributes' => ['data-test' => '*']],
            ],
            'testAllowElements' => [
                '<div class="test" data-test="test">div test</div><span>span test</span>',
                '<div class="test" data-test="test">div test</div>',
                ['allow_safe_elements' => false, 'allow_elements' => ['div' => ['class', 'data-test']]],
            ],
            'testBlockElements' => [
                '<div>div test</div><span>span test</span>',
                '<div>div test</div>span test',
                ['block_elements' => ['span']],
            ],
            'testDropAttributes' => [
                '<a href="https://example.com/" title="test">test</a>',
                '<a href="https://example.com/">test</a>',
                ['drop_attributes' => ['title' => '*']],
            ],
            'testDropElements' => [
                '<div>div test</div><span>span test</span>',
                '<div>div test</div>',
                ['drop_elements' => ['span']],
            ],
            'testClassesAllow' => [
                '<div class="text header">div test</div><span class="text paragraph">span test</span>',
                '<div class="text">div test</div><span class="text">span test</span>',
                ['classes' => ['allow' => ['text']]],
            ],
            'testClassesDrop' => [
                '<div class="text header">div test</div><span class="text paragraph">span test</span>',
                '<div class="text">div test</div><span class="text">span test</span>',
                ['classes' => ['drop' => ['header', 'paragraph']]],
            ],
            'testClassesReplace' => [
                '<div class="text header">div test</div><span class="text paragraph">span test</span>',
                '<div class="text test-header">div test</div><span class="text test-paragraph">span test</span>',
                ['classes' => ['replace' => ['header' => 'test-header', 'paragraph' => 'test-paragraph']]],
            ],
            'testClassesWhitespace' => [
                '<div class="  text-test    header   ">div test</div><span class="     text-test    paragraph   ">span test</span>',
                '<div class="text-test">div test</div><span class="text-test">span test</span>',
                ['classes' => ['allow' => ['text-test']]],
            ],
        ];
    }
}
