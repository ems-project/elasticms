<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\PropertyAccess;

use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\Helpers\Standard\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PropertyAccessRecursiveTest extends TestCase
{
    public static function nestedSourceDataProvider(): array
    {
        $sourceData = [
            'structure' => Json::encode([
                [
                    'id' => 'folder-root',
                    'label' => 'Root',
                    'children' => [
                        [
                            'id' => 'sub-folder-1',
                            'label' => 'Sub 1',
                            'object' => [
                                'title_nl' => 'Gevonden!',
                                'title_fr' => 'Trouvé !',
                            ],
                            'children' => [],
                        ],
                    ],
                ],
            ]),
        ];

        return [[$sourceData]];
    }

    #[DataProvider('nestedSourceDataProvider')]
    public function testIteratorRecursiveWithBeautifulPaths(array $sourceData): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();

        $results = [];
        foreach ($accessor->iterator('[json:id_key:structure][**][title_%locale%]', $sourceData, ['%locale%' => 'nl']) as $path => $value) {
            $results[$path] = $value;
        }

        $expectedPath = '[json:id_key:structure][**][sub-folder-1][object][title_%locale%]';
        $this->assertArrayHasKey($expectedPath, $results);
        $this->assertEquals('Gevonden!', $results[$expectedPath]);
    }

    #[DataProvider('nestedSourceDataProvider')]
    public function testGetValueWithRecursivePath(array $sourceData): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $this->assertEquals('Gevonden!', $accessor->getValue($sourceData, '[json:id_key:structure][**][sub-folder-1][object][title_nl]'));
    }

    #[DataProvider('nestedSourceDataProvider')]
    public function testGetValueWithRecursivePathNotFound(array $sourceData): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $this->assertNull($accessor->getValue($sourceData, '[json:id_key:structure][**][non-existing-uuid][object][title_nl]'));
    }

    #[DataProvider('nestedSourceDataProvider')]
    public function testSetValueWithRecursivePathUpdate(array $sourceData): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $path = '[json:id_key:structure][**][sub-folder-1][object][title_nl]';

        $accessor->setValue($sourceData, $path, 'Aangepast!');

        $this->assertEquals('Aangepast!', $accessor->getValue($sourceData, $path));
    }

    #[DataProvider('nestedSourceDataProvider')]
    public function testSetValueWithRecursivePathInsert(array $sourceData): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();
        $path = '[json:id_key:structure][**][sub-folder-1][object][title_de]';

        $accessor->setValue($sourceData, $path, 'Gefunden!');

        $this->assertEquals('Gefunden!', $accessor->getValue($sourceData, $path));
    }

    #[DataProvider('nestedSourceDataProvider')]
    public function testSetValueWithRecursivePathPreservesOtherFields(array $sourceData): void
    {
        $accessor = PropertyAccessor::createPropertyAccessor();

        $accessor->setValue($sourceData, '[json:id_key:structure][**][sub-folder-1][object][title_nl]', 'Aangepast!');

        $structure = Json::decode($sourceData['structure']);
        $this->assertEquals('Trouvé !', $structure[0]['children'][0]['object']['title_fr']);
        $this->assertEquals('Aangepast!', $structure[0]['children'][0]['object']['title_nl']);
    }
}
