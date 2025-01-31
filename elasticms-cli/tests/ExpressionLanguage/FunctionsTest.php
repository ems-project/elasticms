<?php

declare(strict_types=1);

namespace App\CLI\Tests\ExpressionLanguage;

use App\CLI\ExpressionLanguage\Functions;
use EMS\Helpers\Standard\Json;
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testSimpleDomToJsonMenu()
    {
        $html = '<p>Coucou</p> <h2>Titre</h2> toto <p>foobar</p>';
        $splitted = Functions::domToJsonMenu($html, 'h2', 'body', 'paragraph', 'title');

        $json = Json::decode($splitted);
        $this->assertEquals(2, \count($json));
        $this->assertEquals('Titre', $json[1]['object']['title']);
        $this->assertEquals('Titre', $json[1]['label']);
        $this->assertEquals('Titre', $json[1]['object']['label']);
        $this->assertEquals('<p>Coucou</p> ', $json[0]['object']['body']);
        $this->assertEquals(' toto <p>foobar</p>', $json[1]['object']['body']);
    }

    public function testDivDomToJsonMenu()
    {
        $html = '<div> <p>Coucou</p> <h2>Titre</h2> toto <p>foobar</p></div>';
        $splitted = Functions::domToJsonMenu($html, 'h2', 'body', 'paragraph', 'title');

        $json = Json::decode($splitted);
        $this->assertEquals(2, \count($json));
        $this->assertEquals('Titre', $json[1]['object']['title']);
        $this->assertEquals('Titre', $json[1]['label']);
        $this->assertEquals('Titre', $json[1]['object']['label']);
        $this->assertEquals(' <p>Coucou</p> ', $json[0]['object']['body']);
        $this->assertEquals(' toto <p>foobar</p>', $json[1]['object']['body']);
    }

    public function testLongDomToJsonMenu()
    {
        $html = \file_get_contents(\implode(DIRECTORY_SEPARATOR, [__DIR__, 'div_dom_to_json.html']));
        $splitted = Functions::domToJsonMenu($html, 'h2', 'body', 'paragraph', 'title');
        $json = Json::decode($splitted);
        $this->assertEquals(9, \count($json));
    }
}
