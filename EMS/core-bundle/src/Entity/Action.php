<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\CoreBundle\Core\Action\ActionStatus;
use EMS\Helpers\Standard\Hash;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Action
{
    use CreatedModifiedTrait;

    private UuidInterface $id;
    private ActionStatus $status = ActionStatus::PENDING;

    private string $requestHash;
    /** @var ?array<mixed> */
    private ?array $response = null;

    /**
     * @param array<mixed> $request
     */
    public function __construct(
        private readonly string $sender,
        private readonly string $senderId,
        private readonly string $createdBy,
        /** @var array<mixed> */
        private readonly array $request
    ) {
        $this->id = Uuid::uuid4();
        $this->requestHash = Hash::array($this->request);

        $this->created = new \DateTime();
        $this->modified = new \DateTime();
    }

    /**
     * @return array{
     *     'sender': string,
     *     'senderId': string,
     *     'requestHash': string,
     *     'createdBy': string,
     * }
     */
    public function getInfo(): array
    {
        return [
            'sender' => $this->sender,
            'senderId' => $this->senderId,
            'requestHash' => $this->requestHash,
            'createdBy' => $this->createdBy,
        ];
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /** @return array<mixed> */
    public function getRequest(): array
    {
        return $this->request;
    }

    /** @return ?array<mixed> */
    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function getStatus(): ActionStatus
    {
        return $this->status;
    }

    /** @param ?array<mixed> $response */
    public function setResponse(?array $response): void
    {
        $this->response = $response;
    }

    public function setStatus(ActionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}
