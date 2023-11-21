<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use PHPUnit\Framework\TestCase;

class DummyEntity
{
    use CreatedModifiedTrait;

    public function __construct()
    {
        $this->created = new \DateTime();
    }
}

final class CreatedModifiedTraitAiTest extends TestCase
{
    public function testTimestamps(): void
    {
        $entity = new DummyEntity();

        // Test that the created timestamp is set on creation
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getCreated());

        // Sleep for a second to ensure the modified timestamp will be different
        \sleep(1);

        // Test that the modified timestamp is updated
        $entity->updateModified();
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getModified());
        $this->assertGreaterThan($entity->getCreated(), $entity->getModified());

        // Test setting custom timestamps
        $past = new \DateTime('-1 day');
        $entity->setCreated($past);
        $entity->setModified($past);

        $this->assertEquals($past, $entity->getCreated());
        $this->assertEquals($past, $entity->getModified());
    }
}
