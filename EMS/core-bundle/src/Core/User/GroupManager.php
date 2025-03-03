<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\User;

use EMS\CommonBundle\Entity\EntityInterface;
use EMS\CoreBundle\Entity\Group;
use EMS\CoreBundle\Repository\GroupRepository;
use EMS\CoreBundle\Service\EntityServiceInterface;

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
        return false;
    }

    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        if (null !== $context) {
            throw new \RuntimeException('Unexpected not null context');
        }

        return $this->groupRepository->get($from, $size, $orderField, $orderDirection, $searchValue);
    }

    public function getEntityName(): string
    {
        return 'group';
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
        return $this->groupRepository->getByName($name);
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
        return '';
    }

    public function deleteGroup(Group $group): void
    {
        $this->groupRepository->delete($group);
    }
    public function editGroup(Group $group): void
    {
        $this->groupRepository->edit($group);
    }
    public function deleteAllGroup(): void
    {
        $this->groupRepository->deleteAllGroup();
    }

    public function create(Group $group): void
    {
        if (!$group->isLabelDefined()) {
            $group->setLabel($group->getName());
        }
        $group->setRoles([
            'foo' => 'bar',
        ]);
        $this->groupRepository->save($group);
    }

    public function update(Group $group): void
    {
        $group->setName($group->getName());
        $group->setLabel($group->getName());
        $group->setRoles([]);

        $this->groupRepository->save($group);
    }
}
