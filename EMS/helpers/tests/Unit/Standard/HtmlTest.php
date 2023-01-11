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

    public function testSanitize(): void
    {
        $actual = <<<HTML
            <div class="wrapper">
              <h1 class="title">  Title </h1>
              <hr>
              <p style="color: red"> Hello 
              </p>
              <section><div>section</div></section>
              <footer><div>footer</div></footer>
            </div>
HTML;

        $result = <<<HTML
<div>
  <h1 class="title">
    Title
  </h1>
  <hr>
  <p>
    Hello
  </p>
  <div>
    section
  </div>
</div>
HTML;

        $this->assertEquals($result, (string) (new Html($actual))->sanitize([
            'allow_elements' => [
                ['tag' => 'h1', 'attributes' => ['class']],
            ],
            'block_elements' => ['section'],
            'drop_elements' => ['footer'],
        ])->prettyPrint());
    }
}
