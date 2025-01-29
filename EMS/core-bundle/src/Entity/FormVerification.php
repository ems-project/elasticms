<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Entity;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class FormVerification
{
    private readonly UuidInterface $id;

    private readonly string $code;
    private readonly \DateTime $created;
    private \DateTime $expirationDate;

    private const string EXPIRATION_TIME = '+3 hours';

    public function __construct(private readonly string $value)
    {
        $this->id = Uuid::uuid4();
        $this->created = new \DateTime();
        $this->expirationDate = new \DateTime()->modify(self::EXPIRATION_TIME);
        $this->code = \sprintf('%06d', \random_int(1, 999999));
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function updateExpirationDate(): void
    {
        $this->expirationDate = new \DateTime()->modify(self::EXPIRATION_TIME);
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function getExpirationDate(): \DateTime
    {
        return $this->expirationDate;
    }
}
