<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Html;

use EMS\Helpers\Html\HtmlHelper;
use PHPUnit\Framework\TestCase;

class HtmlHelperTest extends TestCase
{
    public function testInt()
    {
        self::assertEquals(0, HtmlHelper::compare('   <p>Hello<p>', '<p>Hello<p>     '));
    }

    public function testPrettyPrint()
    {
        self::assertEquals('<div>
  <h1>
    Title
  </h1>
  <p>
    Hello
  </p>
</div>', HtmlHelper::prettyPrint('<!-- comment --><div><h1>Title</h1><p>Hello</p></div>'));
    }

    public function testPrettyPrintWithInternalComment()
    {
        self::assertEquals('<div>
  <h1>
    Title
  </h1>
  <p>
    Hello
  </p>
</div>', HtmlHelper::prettyPrint('<div><h1>Title</h1><p>Hello<!--[if IE 6]>Special instructions for IE 6 here<![endif]--></p></div>'));
    }

    public function testPrettyPrintWithConfig()
    {
        self::assertEquals('<div>
  <h1>
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
  </h1>
  <p>
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
    Foobar Foobar
  </p>
</div>', HtmlHelper::prettyPrint('<!-- comment --><div><h1>Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar</h1><p>Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar Foobar</p></div>', ['wrap' => 20]));
    }

    public function testEmptyPrettyPrint()
    {
        self::assertEquals('', HtmlHelper::prettyPrint(''));
    }

    public function testIsHtml()
    {
        self::assertEquals(false, HtmlHelper::isHtml(''));
        self::assertEquals(false, HtmlHelper::isHtml(null));
        self::assertEquals(false, HtmlHelper::isHtml('Hello world'));
        self::assertEquals(false, HtmlHelper::isHtml('Hello world>>>'));
        self::assertEquals(true, HtmlHelper::isHtml('Hello <span>world</span>'));
        self::assertEquals(true, HtmlHelper::isHtml('Hello <span>world</span> <!-- comment -->'));
        self::assertEquals(true, HtmlHelper::isHtml('Hello <!-- comment -->'));
    }
}
