<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Service;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\CommonBundle\Entity\StoreData;
use EMS\CommonBundle\Repository\StoreDataRepository;

class StoreDataEntityService implements StoreDataServiceInterface
{
    public function __construct(private readonly StoreDataRepository $repository, private readonly ?int $ttl = null)
    {
    }

    #[\Override]
    public function save(StoreDataHelper $data): void
    {
        $entity = $this->repository->getByKey($data->getKey());
        if (null === $entity) {
            $entity = new StoreData();
            $entity->setKey($data->getKey());
        }
        $entity->setData($data->getData());
        if (null !== $this->ttl) {
            $entity->expiresAfter($this->ttl);
        }
        $this->repository->update($entity);
    }

    #[\Override]
    public function read(string $key): ?StoreDataHelper
    {
        $entity = $this->repository->getByKey($key);
        if (null === $entity || $entity->isExpired()) {
            return null;
        }

        return new StoreDataHelper($key, $entity->getData());
    }

    #[\Override]
    public function delete(string $key): void
    {
        $entity = $this->repository->getByKey($key);
        if (null === $entity) {
            return;
        }
        $this->repository->delete($entity);
    }

    #[\Override]
    public function gc(): void
    {
        $this->repository->deleteExpired();
    }
}
