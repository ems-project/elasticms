<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Service;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\CommonBundle\Entity\StoreData;
use EMS\CommonBundle\Repository\StoreDataRepository;

class StoreDataEntityService implements StoreDataServiceInterface
{
    public function __construct(private readonly StoreDataRepository $repository)
    {
    }

    public function save(StoreDataHelper $data): void
    {
        $entity = $this->repository->getByKey($data->getKey());
        if (null === $entity) {
            $entity = new StoreData();
            $entity->setKey($data->getKey());
        }
        $entity->setData($data->getData());
        $this->repository->update($entity);
    }

    public function read(string $key): ?StoreDataHelper
    {
        $entity = $this->repository->getByKey($key);
        if (null === $entity) {
            return null;
        }

        return new StoreDataHelper($key, $entity->getData());
    }
}
