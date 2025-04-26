<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class EnvironmentRevision implements \EMS\CommonBundle\Entity\EntityInterface
{
    use CreatedModifiedTrait;

    private UuidInterface $id;
    private Environment $environment;
    private Revision $revision;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function setEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
    }

    public function getRevision(): Revision
    {
        return $this->revision;
    }

    public function setRevision(Revision $revision): void
    {
        $this->revision = $revision;
    }
}
