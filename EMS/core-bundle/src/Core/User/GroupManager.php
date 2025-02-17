<?php

namespace EMS\CoreBundle\Core\User;

use EMS\CommonBundle\Entity\EntityInterface;
use EMS\CoreBundle\Core\Mail\MailerService;
use EMS\CoreBundle\Core\Security\Canonicalizer;
use EMS\CoreBundle\Entity\User;
use EMS\CoreBundle\Repository\GroupRepository;
use EMS\CoreBundle\Service\EntityServiceInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GroupManager implements EntityServiceInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
    ) {
    }
   
    public function getAll(): array
    {
        return $this->groupRepository->getAll();
    }

    public function isSortable(): bool
    {
        // TODO: Implement isSortable() method.
        return false;
    }

    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        // TODO: Implement get() method.
        return [];
    }

    public function getEntityName(): string
    {
        // TODO: Implement getEntityName() method.
        return "";

    }

    public function getAliasesName(): array
    {
        // TODO: Implement getAliasesName() method.
        return [];
    }

    public function count(string $searchValue = '', mixed $context = null): int
    {
        // TODO: Implement count() method.
        return 0;

    }

    public function getByItemName(string $name): ?EntityInterface
    {
        // TODO: Implement getByItemName() method.
        return null;

    }

    public function updateEntityFromJson(EntityInterface $entity, string $json): EntityInterface
    {
        // TODO: Implement updateEntityFromJson() method.
        return $entity;

    }

    public function createEntityFromJson(string $json, ?string $name = null): EntityInterface
    {
        // TODO: Implement createEntityFromJson() method.
        $form = Form::fromJson($json);

        return $form;

    }

    public function deleteByItemName(string $name): string
    {
        // TODO: Implement deleteByItemName() method.
        return "";
    }
}