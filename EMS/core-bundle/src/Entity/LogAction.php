<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\Helpers\Standard\Hash;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LogAction
{
    use CreatedModifiedTrait;

    private UuidInterface $id;

    private string $requestHash;
    /** @var ?array<mixed> */
    private ?array $response = null;

    /**
     * @param array<mixed> $request
     */
    public function __construct(
        /** @var array<mixed> */
        private readonly array $request
    ) {
        $this->id = Uuid::uuid4();
        $this->requestHash = Hash::array($this->request);

        $this->created = new \DateTime();
        $this->modified = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getUuid(): UuidInterface
    {
        return $this->id;
    }

    /** @return array<mixed> */
    public function getRequest(): array
    {
        return $this->request;
    }

    public function getRequestHash(): string
    {
        return $this->requestHash;
    }

    /** @return ?array<mixed> */
    public function getResponse(): ?array
    {
        return $this->response;
    }

    /** @param ?array<mixed> $response */
    public function setResponse(?array $response): void
    {
        $this->response = $response;
    }
}
