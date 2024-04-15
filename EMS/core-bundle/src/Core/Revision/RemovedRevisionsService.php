<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision;

use EMS\CommonBundle\Entity\EntityInterface;
use EMS\CoreBundle\Core\User\UserManager;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Repository\RevisionRepository;
use EMS\CoreBundle\Service\EntityServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RemovedRevisionsService implements EntityServiceInterface
{
    final public const DISCARD_SELECTED_REMOVED_REVISION = 'DISCARD_SELECTED_REMOVED_REVISION';

    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly UserManager $userManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        protected LoggerInterface $logger,
    ) {
    }

    public function isSortable(): bool
    {
        return false;
    }

    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, $context = null): array
    {
        if (null !== $context && !$context instanceof ContentType) {
            throw new \RuntimeException('Unexpected context');
        }

        return $this->revisionRepository->getRemovedRevisions(
            from: $from,
            size: $size,
            orderField: $orderField,
            orderDirection: $orderDirection,
            contentType: $context
        );
    }

    public function getEntityName(): string
    {
        return 'removed_revisions';
    }

    /**
     * @return string[]
     */
    public function getAliasesName(): array
    {
        return [];
    }

    public function count(string $searchValue = '', $context = null): int
    {
        if (null !== $context && !$context instanceof ContentType) {
            throw new \RuntimeException('Unexpected context');
        }

        return $this->revisionRepository->countRemovedRevisions(
            contentType: $context
        );
    }

    public function getByItemName(string $name): ?EntityInterface
    {
        return $this->revisionRepository->findOneById(\intval($name));
    }

    public function updateEntityFromJson(EntityInterface $entity, string $json): EntityInterface
    {
        throw new \RuntimeException('updateEntityFromJson method not yet implemented');
    }

    public function createEntityFromJson(string $json, ?string $name = null): EntityInterface
    {
        throw new \RuntimeException('createEntityFromJson method not yet implemented');
    }

    /**
     * @param string[] $ids
     */
    public function deleteByIds(array $ids): void
    {
        foreach ($this->revisionRepository->getByIds($ids) as $revision) {
            $this->deleteRevision($revision);
        }
    }

    public function deleteRevision(Revision $revision): void
    {
        $name = $revision->getName();
        $this->revisionRepository->delete($revision);
        $this->logger->warning('log.service.revision.delete', [
            'name' => $name,
        ]);
    }

    public function deleteByItemName(string $name): string
    {
        $removedRevision = $this->getByItemName($name);
        if (null === $removedRevision) {
            throw new \RuntimeException(\sprintf('Removed revision %s not found', $name));
        }
        if (!$removedRevision instanceof Revision) {
            throw new \RuntimeException('Unexpected non Removed revision object');
        }
        $id = $removedRevision->getId();
        $this->revisionRepository->delete($removedRevision);

        return \strval($id);
    }
}
