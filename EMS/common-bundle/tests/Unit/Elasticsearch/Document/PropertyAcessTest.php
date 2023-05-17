<?php

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Document\Document;
use PHPUnit\Framework\TestCase;

class PropertyAcessTest extends TestCase
{

    public function testDocumentFieldPathToPropertyPathWithHash(): void
    {
        $this->assertEquals('[fr][content][title]', Document::fieldPathToPropertyPath('fr.content.title'));
        $this->assertEquals('[fr][content]#[title]', Document::fieldPathToPropertyPath('fr.content#title'));
        $this->assertEquals('[foobar]', Document::fieldPathToPropertyPath('foobar'));
        $this->assertEquals('[foobar][0]', Document::fieldPathToPropertyPath('foobar.0'));
        $this->assertEquals('[foobar][0][fr]#[meta][description]', Document::fieldPathToPropertyPath('foobar.0.fr#meta.description'));
    }
}
