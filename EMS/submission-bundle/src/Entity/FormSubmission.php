<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use EMS\CommonBundle\Entity\EntityInterface;
use EMS\SubmissionBundle\Request\DatabaseRequest;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="form_submission")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class FormSubmission implements EntityInterface
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(name="instance", type="string", length=255)
     */
    private string $instance;

    /**
     * @ORM\Column(name="locale", type="string", length=2)
     */
    private string $locale;

    /**
     * @var array<string, mixed>|null
     *
     * @ORM\Column(name="data", type="json", nullable=true)
     */
    private ?array $data;

    /**
     * @var Collection<int, FormSubmissionFile>
     *
     * @ORM\OneToMany(targetEntity="FormSubmissionFile", mappedBy="formSubmission", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * @ORM\Column(name="expire_date", type="date", nullable=true)
     */
    private ?\DateTime $expireDate;

    /**
     * @ORM\Column(name="label", type="string", length=255)
     */
    private string $label;

    /**
     * @ORM\Column(name="process_try_counter", type="integer", nullable=false, options={"default": 0})
     */
    private int $processTryCounter = 0;

    /**
     * @ORM\Column(name="process_id", type="string", length=255, nullable=true)
     */
    private ?string $processId = null;

    /**
     * @ORM\Column(name="process_by", type="string", length=255, nullable=true)
     */
    private ?string $processBy = null;

    public function __construct(DatabaseRequest $databaseRequest)
    {
        $now = new \DateTime();

        $this->id = Uuid::uuid4();
        $this->created = $now;
        $this->modified = $now;

        $this->name = $databaseRequest->getFormName();
        $this->instance = $databaseRequest->getInstance();
        $this->locale = $databaseRequest->getLocale();
        $this->data = $databaseRequest->getData();

        $this->files = new ArrayCollection();

        $this->label = $databaseRequest->getLabel();
        $this->expireDate = $databaseRequest->getExpireDate();

        foreach ($databaseRequest->getFiles() as $file) {
            $this->files->add(new FormSubmissionFile($this, $file));
        }
    }

    public function getId(): string
    {
        return $this->id->toString();
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
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @return Collection<int, FormSubmissionFile>|FormSubmissionFile[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getExpireDate(): ?\DateTime
    {
        return $this->expireDate;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function process(string $username): void
    {
        $this->data = null;
        ++$this->processTryCounter;
        $this->processBy = $username;
        $this->files->clear();
    }

    public function getProcessTryCounter(): int
    {
        return $this->processTryCounter;
    }

    public function setProcessTryCounter(int $processTryCounter): FormSubmission
    {
        $this->processTryCounter = $processTryCounter;

        return $this;
    }

    public function getProcessId(): ?string
    {
        return $this->processId;
    }

    public function setProcessId(string $processId): FormSubmission
    {
        $this->processId = $processId;

        return $this;
    }

    public function getModified(): \DateTime
    {
        return $this->modified;
    }

    public function getProcessBy(): string
    {
        if (null === $this->processBy) {
            throw new \RuntimeException('Unexpected null processBy');
        }

        return $this->processBy;
    }
}
