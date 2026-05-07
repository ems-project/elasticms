<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Twig;

use EMS\CommonBundle\Common\Text\EmsHtml;
use PHPUnit\Framework\TestCase;

class EmsHtmlTest extends TestCase
{
    public function testRemoveTags()
    {
        $html = <<<HTML
            <p>Intro <a href="https://github.com/ems-project/elasticms">elasticms</a> <strong>end</strong></p>
            HTML;

        $this->assertEquals(
            '<p>Intro elasticms end</p>',
            (string) new EmsHtml($html)
                ->removeTag('a')->removeTag('strong')
        );
    }

    public function testRemoveExternalLinks()
    {
        $html = <<<HTML
            <p><a href="https://github.com/ems-project/elasticms">elasticms</a> <a href="/home">Home</a></p>
            HTML;

        $this->assertEquals(
            '<p>elasticms <a href="/home">Home</a></p>',
            (string) new EmsHtml($html)
                ->removeTag('a', '[^>]*href="(https:.*?)"[^>]*')
        );
    }

    public function testPrintUrls()
    {
        $html = <<<HTML
            <p><a href="https://github.com/ems-project/elasticms">elasticms</a> <a href="/home">Home</a></p>
            HTML;

        $this->assertEquals(
            '<p>elasticms (https://github.com/ems-project/elasticms) Home (/home)</p>',
            (string) new EmsHtml($html)
                ->printUrls()
        );
    }

    public function testPrintUrlsKeepAnchors()
    {
        $html = <<<HTML
            <p><a href="#home">home</a> <a id="Home">Home</a></p>
            HTML;

        $this->assertEquals(
            '<p><a href="#home">home</a> <a id="Home">Home</a></p>',
            (string) new EmsHtml($html)->printUrls()
        );
    }

    public function testKeepsEmsLInk()
    {
        $html = <<<HTML
            <ul>
                <li><a href="ems://object:page:y94zwnkB8jQqOH6OrHCL">Internal link</a></li>
                <li><a href="ems://asset:9a323a5b625999e1a13ed88e0b14c86cf0e76edb?name=foobar.txt&type=text/plain">File link</a></li>
            </ul>
            HTML;
        
        
        $this->assertEquals(
            $html,
            (string) new EmsHtml($html)->removeTag('b')
        );
    }
}
