<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Text;

use EMS\CommonBundle\Common\Text\EmsHtml;
use PHPUnit\Framework\TestCase;

class EmsHtmlAiTest extends TestCase
{
    public function testRemoveTag(): void
    {
        $html = new EmsHtml('<div class="test">Hello <span>World</span></div>');
        $html->removeTag('span', '.*?', false);

        $this->assertEquals('<div class="test">Hello </div>', (string) $html);
    }

    public function testRemoveTagKeepContent(): void
    {
        $html = new EmsHtml('<div class="test">Hello <span>World</span></div>');
        $html->removeTag('span');

        $this->assertEquals('<div class="test">Hello World</div>', (string) $html);
    }

    public function testPrintUrls(): void
    {
        $html = new EmsHtml('<a href="https://example.com">Example</a> and <a href="#anchor">Anchor</a>');
        $html->printUrls();

        $this->assertEquals('Example (https://example.com) and <a href="#anchor">Anchor</a>', (string) $html);
    }

    public function testReplace(): void
    {
        $html = new EmsHtml('<div>Hello World</div>');
        $html->replace(['Hello' => 'Hi', 'World' => 'Everyone']);

        $this->assertEquals('<div>Hi Everyone</div>', (string) $html);
    }
}
