<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\CommonBundle\Entity\EntityInterface;
use EMS\Helpers\Standard\Base64;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class FormSubmissionFile implements EntityInterface, \JsonSerializable
{
    use CreatedModifiedTrait;

    private readonly UuidInterface $id;

    /**
     * @var resource|string
     * @phpstan-ignore-next-line
     */
    private $file;
    private readonly string $filename;
    private readonly string $formField;
    private readonly string $mimeType;
    private readonly string $size;

    /**
     * @param array<string, string> $file
     */
    public function __construct(
        private readonly FormSubmission $formSubmission,
        array $file,
    ) {
        $this->id = Uuid::uuid4();
        $this->created = new \DateTime();
        $this->modified = new \DateTime();

        $this->file = Base64::decode($file['base64']);
        $this->filename = $file['filename'];
        $this->formField = $file['form_field'];
        $this->mimeType = $file['mimeType'];
        $this->size = (string) $file['size'];
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'filename' => $this->getFilename(),
            'mimeType' => $this->getMimeType(),
            'size' => $this->getSize(),
            'formField' => $this->getFormField(),
        ];
    }

    /**
     * @return resource|null
     */
    public function getFile()
    {
        return \is_resource($this->file) ? $this->file : null;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getName(): string
    {
        return \sprintf('%s:%s', $this->getFormSubmission()->getName(), $this->filename);
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getFormSubmission(): FormSubmission
    {
        return $this->formSubmission;
    }

    public function getFormField(): string
    {
        return $this->formField;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): string
    {
        return $this->size;
    }
}
