<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\CommonBundle\Entity\EntityInterface;
use EMS\Helpers\Standard\DateTime;
use EMS\SubmissionBundle\Request\DatabaseRequest;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class FormSubmission implements EntityInterface, \JsonSerializable
{
    use CreatedModifiedTrait;

    private readonly UuidInterface $id;
    private readonly string $name;
    private readonly string $instance;
    private readonly string $locale;

    /** @var array<string, mixed>|null */
    private ?array $data;
    /** @var Collection<int, FormSubmissionFile> */
    protected Collection $files;

    private readonly ?\DateTimeInterface $expireDate;
    private readonly string $label;
    private int $processTryCounter = 0;
    private ?string $processId = null;
    private ?string $processBy = null;

    public function __construct(DatabaseRequest $databaseRequest)
    {
        $this->id = Uuid::uuid4();
        $this->created = DateTime::create('now');
        $this->modified = DateTime::create('now');

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
        $data = \get_object_vars($this);
        $data['created'] = $this->created->format(\DateTimeInterface::ATOM);
        $data['modified'] = $this->modified->format(\DateTimeInterface::ATOM);

        if ($this->expireDate) {
            $data['expireDate'] = $this->expireDate->format(\DateTimeInterface::ATOM);
        } else {
            unset($data['expireDate']);
        }

        if ($this->files->count() > 0) {
            $files = $this->files->toArray();
            $data['files'] = \array_map(static fn (FormSubmissionFile $f) => $f->toArray(), $files);
        } else {
            unset($data['files']);
        }

        return $data;
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @return Collection<int, FormSubmissionFile>
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

    public function getExpireDate(): ?\DateTimeInterface
    {
        return $this->expireDate;
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

    public function getProcessBy(): string
    {
        if (null === $this->processBy) {
            throw new \RuntimeException('Unexpected null processBy');
        }

        return $this->processBy;
    }

    public function hasBeenProcessed(): bool
    {
        return null !== $this->processBy;
    }
}
