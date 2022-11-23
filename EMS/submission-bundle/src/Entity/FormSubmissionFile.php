<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EMS\CommonBundle\Entity\EntityInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="form_submission_file")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class FormSubmissionFile implements EntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="created", type="datetime")
     */
    private \DateTime $created;

    /**
     * @ORM\Column(name="modified", type="datetime")
     */
    private \DateTime $modified;

    /**
     * @ORM\ManyToOne(targetEntity="EMS\SubmissionBundle\Entity\FormSubmission", inversedBy="files")
     * @ORM\JoinColumn(name="form_submission_id", referencedColumnName="id")
     */
    private FormSubmission $formSubmission;

    /**
     * @var string|resource
     *
     * @ORM\Column(name="file", type="blob")
     */
    private $file;

    /**
     * @ORM\Column(name="filename", type="string")
     */
    private string $filename;

    /**
     * @ORM\Column(name="form_field", type="string")
     */
    private string $formField;

    /**
     * @ORM\Column(name="mime_type", type="string", length=1024)
     */
    private string $mimeType;

    /**
     * @ORM\Column(name="size", type="bigint")
     */
    private string $size;

    /**
     * @param array<string, string> $file
     */
    public function __construct(FormSubmission $formSubmission, array $file)
    {
        $now = new \DateTime();

        $this->id = Uuid::uuid4();
        $this->created = $now;
        $this->modified = $now;

        $this->formSubmission = $formSubmission;
        $this->file = \base64_decode($file['base64']);
        $this->filename = $file['filename'];
        $this->formField = $file['form_field'];
        $this->mimeType = $file['mimeType'];
        $this->size = $file['size'];
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateModified(): void
    {
        $this->modified = new \DateTime();
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

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function getModified(): \DateTime
    {
        return $this->modified;
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

    /**
     * @return mixed|string
     */
    public function getSize()
    {
        return $this->size;
    }
}
