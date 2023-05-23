<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\PropertyAccess;

use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\Helpers\Standard\Json;
use PHPUnit\Framework\TestCase;

class PropertyAcessTest extends TestCase
{
    public function testDocumentFieldPathToPropertyPathWithHash(): void
    {
        $this->assertEquals('[fr][content][title]', Document::fieldPathToPropertyPath('fr.content.title'));
        $this->assertEquals('[fr][content][json:title]', Document::fieldPathToPropertyPath('fr.content.json:title'));
        $this->assertEquals('[foobar]', Document::fieldPathToPropertyPath('foobar'));
        $this->assertEquals('[foobar][0]', Document::fieldPathToPropertyPath('foobar.0'));
        $this->assertEquals('[foobar][0][fr][json:meta][description]', Document::fieldPathToPropertyPath('foobar.0.fr.json:meta.description'));
    }

    public function testSetter(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [];
        $accessor->setValue($array, '[foobar][barfoo]', 'value');
        $this->assertEquals(['foobar' => ['barfoo' => 'value']], $array);
        $accessor->setValue($array, '[foobar][barfoo]', 'value2');
        $this->assertEquals(['foobar' => ['barfoo' => 'value2']], $array);
        $accessor->setValue($array, '[fr][json:content][title]', 'title value');
        $this->assertEquals([
            'foobar' => ['barfoo' => 'value2'],
            'fr' => [
                'content' => Json::encode([
                    'title' => 'title value',
                ]),
            ]], $array);
        $accessor->setValue($array, '[nl][json:content][title]', 'title value nl');
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
        $this->assertEquals('title value nl', $accessor->getValue($array, '[nl][json:content][title]'));
        $this->assertEquals(null, $accessor->getValue($array, '[de][json:content][title]'));
    }

    public function testWithSeparatorIterator(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [
            'fr' => 'title fr',
            'nl' => 'title nl',
            'de' => 'title de',
        ];

        $counter = 0;
        $expected = [
            'nl',
            'fr',
            'de',
        ];
        foreach ($accessor->iterator('[nl|fr|de|en]', $array) as $path => $value) {
            $this->assertEquals("[$expected[$counter]]", $path);
            $this->assertEquals($array[$expected[$counter]], $value);
            ++$counter;
        }
        $this->assertEquals(3, $counter);
    }

    public function testWithOneWildCharIterator(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [
            'foobar' => ['barfoo' => 'value2'],
            'fr' => [
                'content' => Json::encode([
                    ['label' => 'label 1'],
                    ['label' => 'label 2'],
                    ['label' => 'label 3'],
                    ['label' => 'label 4'],
                    ['label' => 'label 5'],
                ]),
            ],
            'nl' => [
                'content' => Json::encode([
                    ['label' => 'label nl 1'],
                    ['label' => 'label nl 2'],
                    ['label' => 'label nl 3'],
                    ['label' => 'label nl 4'],
                    ['label' => 'label nl 5'],
                ]),
            ]];

        $expected = [
            '[fr][json:content][0][label]' => 'label 1',
            '[fr][json:content][1][label]' => 'label 2',
            '[fr][json:content][2][label]' => 'label 3',
            '[fr][json:content][3][label]' => 'label 4',
            '[fr][json:content][4][label]' => 'label 5',
            '[nl][json:content][0][label]' => 'label nl 1',
            '[nl][json:content][1][label]' => 'label nl 2',
            '[nl][json:content][2][label]' => 'label nl 3',
            '[nl][json:content][3][label]' => 'label nl 4',
            '[nl][json:content][4][label]' => 'label nl 5',
        ];
        $counter = 0;

        foreach ($accessor->iterator('[fr|nl][json:content][*][label]', $array) as $propertyPath => $value) {
            $this->assertEquals($expected[$propertyPath], $value);
            $this->assertEquals($value, $accessor->getValue($array, $propertyPath));
            ++$counter;
        }
        $this->assertEquals(10, $counter);
    }

    public function testWithJsonNestedEncoded(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [
            'codes' => '[{"id":"742a85b3-46f7-4e46-b2e8-444fc29a8ea1","label":"TEST MDK / TEST MDK (19/05/2023 - )","type":"code","object":{"validity_start":"2023-05-19T11:00:55+0200","title_nl":"TEST MDK","title_fr":"TEST MDK","meaning_nl":"TEST MDK","meaning_fr":"TEST MDK","remarks_nl":"TEST MDK","remarks_fr":"TEST MDK","label":"TEST MDK / TEST MDK (19/05/2023 - )"},"children":[]}]',
        ];

        $expected = [
            '[json:codes][0][object][title_fr]' => 'TEST MDK',
            '[json:codes][0][object][meaning_fr]' => 'TEST MDK',
            '[json:codes][0][object][remarks_fr]' => 'TEST MDK',
        ];
        $counter = 0;

        foreach ($accessor->iterator('[json:codes][*][object][title_fr|meaning_fr|remarks_fr]', $array) as $propertyPath => $value) {
            $this->assertEquals($expected[$propertyPath], $value);
            $this->assertEquals($value, $accessor->getValue($array, $propertyPath));
            ++$counter;
        }
        $this->assertEquals(3, $counter);
    }

    public function testidKeyOperator(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [
            'codes' => '[{"id":"742a85b3-46f7-4e46-b2e8-444fc29a8ea1","label":"TEST MDK / TEST MDK (19/05/2023 - )","type":"code","object":{"validity_start":"2023-05-19T11:00:55+0200","title_nl":"TEST MDK","title_fr":"TEST MDK","meaning_nl":"TEST MDK","meaning_fr":"TEST MDK","remarks_nl":"TEST MDK","remarks_fr":"TEST MDK","label":"TEST MDK / TEST MDK (19/05/2023 - )"},"children":[]},{"id":"91d68620-558d-4cdd-8ced-79b622244fa6","label":"","type":"code","object":{"validity_start":"2023-05-17T12:23:42+0200","validity_end":"2023-05-17T12:23:42+0200","title_fr":"Title FR","meaning_fr":"Meaning FR","remarks_fr":"Remarks FR","label":""},"children":[]}]',
        ];

        $expected = [
            '[json:id_key:codes][742a85b3-46f7-4e46-b2e8-444fc29a8ea1][object][title_fr]' => 'TEST MDK',
            '[json:id_key:codes][742a85b3-46f7-4e46-b2e8-444fc29a8ea1][object][meaning_fr]' => 'TEST MDK',
            '[json:id_key:codes][742a85b3-46f7-4e46-b2e8-444fc29a8ea1][object][remarks_fr]' => 'TEST MDK',
            '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][title_fr]' => 'Title FR',
            '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][meaning_fr]' => 'Meaning FR',
            '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][remarks_fr]' => 'Remarks FR',
        ];
        $counter = 0;

        foreach ($accessor->iterator('[json:id_key:codes][*][object][title_fr|meaning_fr|remarks_fr]', $array) as $propertyPath => $value) {
            $this->assertEquals($expected[$propertyPath], $value);
            $this->assertEquals($value, $accessor->getValue($array, $propertyPath));
            ++$counter;
        }
        $this->assertEquals(\count($expected), $counter);

        $accessor->setValue($array, '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][title_nl]', 'Title NL');
        $accessor->setValue($array, '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][meaning_nl]', 'Meaning NL');
        $accessor->setValue($array, '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][remarks_nl]', 'Remarks NL');

        $this->assertEquals('[{"id":"742a85b3-46f7-4e46-b2e8-444fc29a8ea1","label":"TEST MDK \/ TEST MDK (19\/05\/2023 - )","type":"code","object":{"validity_start":"2023-05-19T11:00:55+0200","title_nl":"TEST MDK","title_fr":"TEST MDK","meaning_nl":"TEST MDK","meaning_fr":"TEST MDK","remarks_nl":"TEST MDK","remarks_fr":"TEST MDK","label":"TEST MDK \/ TEST MDK (19\/05\/2023 - )"},"children":[]},{"id":"91d68620-558d-4cdd-8ced-79b622244fa6","label":"","type":"code","object":{"validity_start":"2023-05-17T12:23:42+0200","validity_end":"2023-05-17T12:23:42+0200","title_fr":"Title FR","meaning_fr":"Meaning FR","remarks_fr":"Remarks FR","label":"","title_nl":"Title NL","meaning_nl":"Meaning NL","remarks_nl":"Remarks NL"},"children":[]}]', $array['codes']);
    }

    public function testIteratorWithReplacers(): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $array = [
            'codes' => '[{"id":"742a85b3-46f7-4e46-b2e8-444fc29a8ea1","label":"TEST MDK / TEST MDK (19/05/2023 - )","type":"code","object":{"validity_start":"2023-05-19T11:00:55+0200","title_nl":"TEST MDK","title_fr":"TEST MDK","meaning_nl":"TEST MDK","meaning_fr":"TEST MDK","remarks_nl":"TEST MDK","remarks_fr":"TEST MDK","label":"TEST MDK / TEST MDK (19/05/2023 - )"},"children":[]},{"id":"91d68620-558d-4cdd-8ced-79b622244fa6","label":"","type":"code","object":{"validity_start":"2023-05-17T12:23:42+0200","validity_end":"2023-05-17T12:23:42+0200","title_fr":"Title FR","meaning_fr":"Meaning FR","remarks_fr":"Remarks FR","label":""},"children":[]}]',
        ];

        $expected = [
            '[json:id_key:codes][742a85b3-46f7-4e46-b2e8-444fc29a8ea1][object][title_%locale%]' => 'TEST MDK',
            '[json:id_key:codes][742a85b3-46f7-4e46-b2e8-444fc29a8ea1][object][meaning_%locale%]' => 'TEST MDK',
            '[json:id_key:codes][742a85b3-46f7-4e46-b2e8-444fc29a8ea1][object][remarks_%locale%]' => 'TEST MDK',
            '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][title_%locale%]' => 'Title FR',
            '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][meaning_%locale%]' => 'Meaning FR',
            '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][remarks_%locale%]' => 'Remarks FR',
        ];

        $counter = 0;
        foreach ($accessor->iterator('[json:id_key:codes][*][object][title_%locale%|meaning_%locale%|remarks_%locale%]', $array, ['%locale%' => 'fr']) as $propertyPath => $value) {
            $this->assertEquals($expected[$propertyPath], $value);
            $this->assertEquals($value, $accessor->getValue($array, \str_replace('%locale%', 'fr', $propertyPath)));
            ++$counter;
        }
        $this->assertEquals(\count($expected), $counter);

        $accessor->setValue($array, '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][title_nl]', 'Title NL');
        $accessor->setValue($array, '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][meaning_nl]', 'Meaning NL');
        $accessor->setValue($array, '[json:id_key:codes][91d68620-558d-4cdd-8ced-79b622244fa6][object][remarks_nl]', 'Remarks NL');

        $this->assertEquals('[{"id":"742a85b3-46f7-4e46-b2e8-444fc29a8ea1","label":"TEST MDK \/ TEST MDK (19\/05\/2023 - )","type":"code","object":{"validity_start":"2023-05-19T11:00:55+0200","title_nl":"TEST MDK","title_fr":"TEST MDK","meaning_nl":"TEST MDK","meaning_fr":"TEST MDK","remarks_nl":"TEST MDK","remarks_fr":"TEST MDK","label":"TEST MDK \/ TEST MDK (19\/05\/2023 - )"},"children":[]},{"id":"91d68620-558d-4cdd-8ced-79b622244fa6","label":"","type":"code","object":{"validity_start":"2023-05-17T12:23:42+0200","validity_end":"2023-05-17T12:23:42+0200","title_fr":"Title FR","meaning_fr":"Meaning FR","remarks_fr":"Remarks FR","label":"","title_nl":"Title NL","meaning_nl":"Meaning NL","remarks_nl":"Remarks NL"},"children":[]}]', $array['codes']);
    }
}
