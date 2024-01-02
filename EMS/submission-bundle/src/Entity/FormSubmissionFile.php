<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\CommonBundle\Entity\EntityInterface;
use EMS\Helpers\Standard\Base64;
use EMS\Helpers\Standard\DateTime;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class FormSubmissionFile implements EntityInterface
{
    use CreatedModifiedTrait;

    private readonly UuidInterface $id;
    /** @var string|resource */
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
        array $file
    ) {
        $this->id = Uuid::uuid4();
        $this->created = DateTime::create('now');
        $this->modified = DateTime::create('now');

        $this->file = Base64::decode($file['base64']);
        $this->filename = $file['filename'];
        $this->formField = $file['form_field'];
        $this->mimeType = $file['mimeType'];
        $this->size = (string) $file['size'];
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
