<?php

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\PropertyAccessor;
use EMS\Helpers\Standard\Json;
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

    public function testSetter(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [];
        $accessor->setValue($array, '[foobar][barfoo]', 'value');
        $this->assertEquals(['foobar' => ['barfoo' => 'value']], $array);
        $accessor->setValue($array, '[foobar][barfoo]', 'value2');
        $this->assertEquals(['foobar' => ['barfoo' => 'value2']], $array);
        $accessor->setValue($array, '[fr][content]#[title]', 'title value');
        $this->assertEquals([
            'foobar' => ['barfoo' => 'value2'],
            'fr' => [
                'content' => Json::encode([
                    'title' => 'title value',
                ]),
            ]], $array);
        $accessor->setValue($array, '[nl][content]#[title]', 'title value nl');
        $this->assertEquals([
            'foobar' => ['barfoo' => 'value2'],
            'fr' => [
                'content' => Json::encode([
                    'title' => 'title value',
                ]),
            ],
            'nl' => [
                'content' => Json::encode([
                    'title' => 'title value nl',
                ]),
            ]], $array);
    }

    public function testGetter(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [
            'foobar' => ['barfoo' => 'value2'],
            'fr' => [
                'content' => Json::encode([
                    'title' => 'title value',
                ]),
            ],
            'nl' => [
                'content' => Json::encode([
                    'title' => 'title value nl',
                ]),
            ]];
        $this->assertEquals('value2', $accessor->getValue($array, '[foobar][barfoo]'));
        $this->assertEquals('title value nl', $accessor->getValue($array, '[nl][content]#[title]'));
        $this->assertEquals(null, $accessor->getValue($array, '[de][content]#[title]'));
    }
}
