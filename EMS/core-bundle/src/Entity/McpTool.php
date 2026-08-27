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

    final public const string OUTPUT_TYPE_CONTENT_TYPE_ARRAY = 'content_type_array';
    final public const string OUTPUT_TYPE_JOB = 'job';
    final public const string OUTPUT_TYPE_CUSTOM = 'custom';

    private UuidInterface $id;
    protected string $name = '';
    protected string $label = '';
    /** @var string[] */
    protected array $roles = [];
    protected ?string $description = null;
    protected ?string $template = null;
    protected string $outputType = self::OUTPUT_TYPE_CONTENT_TYPE_ARRAY;
    protected ?string $contentType = null;
    protected ?string $customOutput = null;
    protected bool $enabled = true;
    /** @var array<int, array{name?: string, type?: string, description?: string, example?: string}> */
    protected array $inputs = [];

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

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    public function getOutputType(): string
    {
        return $this->outputType;
    }

    public function setOutputType(string $outputType): void
    {
        if (!\in_array($outputType, self::getOutputTypes(), true)) {
            throw new \InvalidArgumentException(\sprintf('Unexpected MCP tool output type "%s".', $outputType));
        }

        $this->outputType = $outputType;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getCustomOutput(): ?string
    {
        return $this->customOutput;
    }

    public function setCustomOutput(?string $customOutput): void
    {
        $this->customOutput = $customOutput;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return array<int, array{name?: string, type?: string, description?: string, example?: string}>
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /**
     * @param array<int, array{name?: string, type?: string, description?: string, example?: string}> $inputs
     */
    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
    }

    /**
     * @return string[]
     */
    public static function getOutputTypes(): array
    {
        return [
            self::OUTPUT_TYPE_CONTENT_TYPE_ARRAY,
            self::OUTPUT_TYPE_JOB,
            self::OUTPUT_TYPE_CUSTOM,
        ];
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
