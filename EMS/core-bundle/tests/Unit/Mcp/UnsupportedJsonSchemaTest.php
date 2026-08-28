<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Tests\Unit\Mcp;

use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Form\DataField\JsonMenuEditorFieldType;
use EMS\CoreBundle\Service\ElasticsearchService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UnsupportedJsonSchemaTest extends TestCase
{
    public function testUnsupportedFieldSchemaUsesExplicitJsonTypes(): void
    {
        $fieldType = new JsonMenuEditorFieldType(
            $this->createStub(AuthorizationCheckerInterface::class),
            $this->createStub(FormRegistryInterface::class),
            $this->createStub(ElasticsearchService::class),
        );

        $schema = $fieldType->generateJsonSchema(new FieldType(), static fn (array $fieldTypes): array => []);

        self::assertSame(['object', 'array', 'string', 'number', 'boolean', 'null'], $schema['type']);
        self::assertSame(\sprintf('ElasticMS field type "%s".', JsonMenuEditorFieldType::class), $schema['description']);
    }
}
