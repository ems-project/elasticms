<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service;

use EMS\CommonBundle\Entity\EntityInterface;
use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Repository\ContentTypeRepository;
use Psr\Log\LoggerInterface;

final class TrashService implements EntityServiceInterface
{
    public function __construct(private readonly ContentTypeRepository $contentTypeRepository, private readonly LoggerInterface $logger)
    {
    }

    /**
     * @return ContentType[]
     */
    public function getAll(): array
    {
        return $this->contentTypeRepository->getAll();
    }

    public function isSortable(): bool
    {
        return true;
    }

    /**
     * @param mixed $context
     *
     * @return ContentType[]
     */
    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, $context = null): array
    {
        if (null !== $context) {
            throw new \RuntimeException('Unexpected context');
        }

        return $this->contentTypeRepository->get($from, $size, $orderField, $orderDirection, $searchValue);
    }

    public function getEntityName(): string
    {
        return 'contentType';
    }

    /**
     * @return string[]
     */
    public function getAliasesName(): array
    {
        return [
            'contentTypes',
            'ContentType',
            'ContentTypes',
        ];
    }

    /**
     * @param mixed $context
     */
    public function count(string $searchValue = '', $context = null): int
    {
        if (null !== $context) {
            throw new \RuntimeException('Unexpected non-null object');
        }

        return $this->contentTypeRepository->counter($searchValue);
    }

    public function getByItemName(string $name): ?EntityInterface
    {
        return $this->contentTypeRepository->getByName($name);
    }

    public function update(ContentType $contentType): void
    {
        if (0 === $contentType->getOrderKey()) {
            $contentType->setOrderKey($this->contentTypeRepository->counter() + 1);
        }
        $encoder = new Encoder();
        $webalized = $encoder->webalize($contentType->getName());
        $contentType->setName($webalized);
        $this->contentTypeRepository->create($contentType);
    }

    public function updateEntityFromJson(EntityInterface $entity, string $json): EntityInterface
    {
        $schedule = ContentType::fromJson($json, $entity);
        $this->update($schedule);

        return $schedule;
    }

    public function createEntityFromJson(string $json, ?string $name = null): EntityInterface
    {
        $contentType = ContentType::fromJson($json);
        if (null !== $name && $contentType->getName() !== $name) {
            throw new \RuntimeException(\sprintf('Filter name mismatched: %s vs %s', $contentType->getName(), $name));
        }
        $this->update($contentType);

        return $contentType;
    }

    public function delete(ContentType $contentType): void
    {
        $name = $contentType->getName();
        $this->contentTypeRepository->delete($contentType);
        $this->logger->warning('log.service.trash.delete', [
            'name' => $name,
        ]);
    }

    public function deleteByItemName(string $name): string
    {
        $contentType = $this->contentTypeRepository->getByName($name);
        if (null === $contentType) {
            throw new \RuntimeException(\sprintf('Filter %s not found', $name));
        }
        $id = $contentType->getId();
        $label = $contentType->getLabelField();
        $this->delete($contentType);
        $this->logger->warning('log.service.action.delete', [
            'name' => $name,
            'label' => $label,
        ]);

        return \strval($id);
    }

    /**
     * @param string[] $ids
     */
    public function deleteByIds(array $ids): void
    {
        foreach ($this->contentTypeRepository->getByIds($ids) as $contentType) {
            $this->delete($contentType);
        }
    }

    /**
     * @param string[] $ids
     */
    public function reorderByIds(array $ids): void
    {
        $counter = 1;
        foreach ($ids as $id) {
            $channel = $this->contentTypeRepository->getById($id);
            $channel->setOrderKey($counter++);
            $this->contentTypeRepository->create($channel);
        }
    }
}
