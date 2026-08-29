<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Tests\Unit\Mcp;

use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Form\DataField\IconFieldType;
use EMS\CoreBundle\Service\ElasticsearchService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UnsupportedJsonSchemaTest extends TestCase
{
    public function testUnsupportedFieldSchemaUsesExplicitJsonTypes(): void
    {
        $fieldType = new IconFieldType(
            $this->createStub(AuthorizationCheckerInterface::class),
            $this->createStub(FormRegistryInterface::class),
            $this->createStub(ElasticsearchService::class),
        );

        $schema = $fieldType->generateJsonSchema(new FieldType(), static fn (array $fieldTypes): array => []);

        self::assertSame([
            'anyOf' => [
                ['type' => 'object'],
                ['type' => 'array'],
                ['type' => 'string'],
                ['type' => 'number'],
                ['type' => 'boolean'],
                ['type' => 'null'],
            ],
        ], $schema['type']);
        self::assertSame(\sprintf('ElasticMS field type "%s".', IconFieldType::class), $schema['description']);
    }
}
