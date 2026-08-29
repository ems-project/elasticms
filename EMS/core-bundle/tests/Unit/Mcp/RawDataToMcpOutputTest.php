<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Tests\Unit\Mcp;

use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\DataField;
use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Form\DataField\JsonMenuNestedEditorFieldType;
use EMS\CoreBundle\Form\DataField\MultiplexedTabContainerFieldType;
use EMS\CoreBundle\Form\DataField\NestedFieldType;
use EMS\CoreBundle\Form\DataField\TextStringFieldType;
use EMS\CoreBundle\Mcp\ElasticmsMcpToolDataService;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\ElasticsearchService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use EMS\Helpers\Standard\Json;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RawDataToMcpOutputTest extends TestCase
{
    public function testItLoadsRevisionDataFieldStructureWhenMissing(): void
    {
        $labelField = new FieldType()->setName('label')->setType(TextStringFieldType::class);
        $slugField = new FieldType()->setName('slug')->setType(TextStringFieldType::class);
        $pageComponentField = new FieldType()->setName('page')->setType(NestedFieldType::class);
        $pageComponentField->addChild($labelField)->addChild($slugField);
        $componentsField = new FieldType()->setName('components')->setType(JsonMenuNestedEditorFieldType::class);
        $componentsField->addChild($pageComponentField);

        $localesField = new FieldType()
            ->setName('locales')
            ->setType(MultiplexedTabContainerFieldType::class)
            ->setOptions(['displayOptions' => ['values' => 'fr']]);
        $localesField->addChild($componentsField);

        $contentTypeRoot = new FieldType()->setName('source')->setType(NestedFieldType::class);
        $contentTypeRoot->addChild($localesField);
        $contentType = new ContentType()->setName('page')->setFieldType($contentTypeRoot);

        $revision = new Revision()
            ->setContentType($contentType)
            ->setRawData([
                'fr' => [
                    'components' => Json::encode([
                        [
                            'id' => 'node-fr',
                            'type' => 'page',
                            'label' => 'Legacy FR',
                            'object' => [
                                'label' => 'Accueil',
                                'slug' => 'accueil',
                            ],
                            'children' => [],
                        ],
                    ]),
                ],
            ]);

        $dataService = $this->createStub(DataService::class);
        $dataService->method('loadDataStructure')->willReturnCallback(function (Revision $revision) use ($localesField): void {
            $rootDataField = new DataField();
            $rootDataField->addChild(new DataField()->setFieldType($localesField));
            $revision->setDataField($rootDataField);
        });

        $registry = $this->createStub(FormRegistryInterface::class);
        $registry->method('getType')->willReturnCallback(function (string $name): ResolvedFormTypeInterface {
            $innerType = match ($name) {
                TextStringFieldType::class => new TextStringFieldType(
                    $this->createStub(AuthorizationCheckerInterface::class),
                    $this->createStub(FormRegistryInterface::class),
                    $this->createStub(ElasticsearchService::class),
                ),
                JsonMenuNestedEditorFieldType::class => new JsonMenuNestedEditorFieldType(
                    $this->createStub(AuthorizationCheckerInterface::class),
                    $this->createStub(FormRegistryInterface::class),
                    $this->createStub(ElasticsearchService::class),
                ),
                default => throw new \RuntimeException(\sprintf('Unexpected type "%s"', $name)),
            };

            $resolvedType = $this->createStub(ResolvedFormTypeInterface::class);
            $resolvedType->method('getInnerType')->willReturn($innerType);

            return $resolvedType;
        });

        $service = new ElasticmsMcpToolDataService(
            $this->createStub(UserService::class),
            $this->createStub(ContentTypeService::class),
            $this->createStub(RevisionService::class),
            $dataService,
            $registry,
            $this->createStub(AuthorizationCheckerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $method = new \ReflectionMethod(ElasticmsMcpToolDataService::class, 'rawDataToMcpOutput');
        $output = $method->invoke($service, $revision);

        self::assertSame('Accueil', $output['fr']['components'][0]['label']);
        self::assertSame(['label' => 'Accueil', 'slug' => 'accueil'], $output['fr']['components'][0]['object']);
    }

    public function testItBuildsMcpRawDataRecursivelyIncludingJsonMenuNestedObjectsInLocales(): void
    {
        $registry = $this->createStub(FormRegistryInterface::class);
        $registry->method('getType')->willReturnCallback(function (string $name): ResolvedFormTypeInterface {
            $innerType = match ($name) {
                TextStringFieldType::class => new TextStringFieldType(
                    $this->createStub(AuthorizationCheckerInterface::class),
                    $this->createStub(FormRegistryInterface::class),
                    $this->createStub(ElasticsearchService::class),
                ),
                JsonMenuNestedEditorFieldType::class => new JsonMenuNestedEditorFieldType(
                    $this->createStub(AuthorizationCheckerInterface::class),
                    $this->createStub(FormRegistryInterface::class),
                    $this->createStub(ElasticsearchService::class),
                ),
                default => throw new \RuntimeException(\sprintf('Unexpected type "%s"', $name)),
            };

            $resolvedType = $this->createStub(ResolvedFormTypeInterface::class);
            $resolvedType->method('getInnerType')->willReturn($innerType);

            return $resolvedType;
        });

        $service = new ElasticmsMcpToolDataService(
            $this->createStub(UserService::class),
            $this->createStub(ContentTypeService::class),
            $this->createStub(RevisionService::class),
            $this->createStub(DataService::class),
            $registry,
            $this->createStub(AuthorizationCheckerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $labelField = new FieldType()->setName('label')->setType(TextStringFieldType::class);
        $slugField = new FieldType()->setName('slug')->setType(TextStringFieldType::class);
        $pageComponentField = new FieldType()->setName('page')->setType(NestedFieldType::class);
        $pageComponentField->addChild($labelField)->addChild($slugField);
        $componentsField = new FieldType()->setName('components')->setType(JsonMenuNestedEditorFieldType::class);
        $componentsField->addChild($pageComponentField);

        $localesField = new FieldType()
            ->setName('locales')
            ->setType(MultiplexedTabContainerFieldType::class)
            ->setOptions(['displayOptions' => ['values' => 'fr
nl']]);
        $localesField->addChild($componentsField);

        $rootDataField = new DataField();
        $rootDataField->addChild(new DataField()->setFieldType($localesField));

        $revision = new Revision()
            ->setRawData([
                'fr' => [
                    'components' => Json::encode([
                        [
                            'id' => 'node-fr',
                            'type' => 'page',
                            'label' => 'Legacy FR',
                            'object' => [
                                'label' => 'Accueil',
                                'slug' => 'accueil',
                            ],
                            'children' => [],
                        ],
                    ]),
                ],
                'nl' => [
                    'components' => Json::encode([
                        [
                            'id' => 'node-nl',
                            'type' => 'page',
                            'label' => 'Legacy NL',
                            'object' => [
                                'label' => 'Welkom',
                                'slug' => 'welkom',
                            ],
                            'children' => [],
                        ],
                    ]),
                ],
                '_checksum' => 'ignored',
            ])
            ->setDataField($rootDataField);

        $method = new \ReflectionMethod(ElasticmsMcpToolDataService::class, 'rawDataToMcpOutput');
        $output = $method->invoke($service, $revision);

        self::assertSame('Accueil', $output['fr']['components'][0]['label']);
        self::assertSame(['label' => 'Accueil', 'slug' => 'accueil'], $output['fr']['components'][0]['object']);
        self::assertSame('Welkom', $output['nl']['components'][0]['label']);
        self::assertSame(['label' => 'Welkom', 'slug' => 'welkom'], $output['nl']['components'][0]['object']);
        self::assertArrayNotHasKey('_checksum', $output);
    }
}
