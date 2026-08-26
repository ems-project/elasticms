<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\CoreBundle\Entity\Helper\JsonClass;
use EMS\CoreBundle\Entity\Helper\JsonDeserializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class McpTool extends JsonDeserializer implements \JsonSerializable, EntityInterface
{
    use CreatedModifiedTrait;

    private UuidInterface $id;
    protected string $name = '';
    protected string $label = '';

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
    }

    public static function fromJson(string $json, ?\EMS\CommonBundle\Entity\EntityInterface $mcpTool = null): McpTool
    {
        $meta = JsonClass::fromJsonString($json);
        $mcpTool = $meta->jsonDeserialize($mcpTool);
        if (!$mcpTool instanceof McpTool) {
            throw new \Exception(\sprintf('Unexpected object class, got %s', $meta->getClass()));
        }

        return $mcpTool;
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    #[\Override]
    public function jsonSerialize(): JsonClass
    {
        $json = new JsonClass(\get_object_vars($this), self::class);
        $json->removeProperty('id');
        $json->removeProperty('created');
        $json->removeProperty('modified');

        return $json;
    }
}
