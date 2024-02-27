<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData;

use EMS\CommonBundle\Common\StoreData\Factory\StoreDataFactoryInterface;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use Psr\Log\LoggerInterface;

class StoreDataManager
{
    /** @var StoreDataFactoryInterface[] */
    private array $factories = [];
    /** @var StoreDataServiceInterface[] */
    private array $services;

    /**
     * @param iterable<StoreDataFactoryInterface> $factories
     * @param array<array{type: string}>          $storeDataConfigs
     */
    public function __construct(private readonly LoggerInterface $logger, iterable $factories, private readonly array $storeDataConfigs = [])
    {
        foreach ($factories as $factory) {
            if (!$factory instanceof StoreDataFactoryInterface) {
                throw new \RuntimeException('Unexpected StoreDataFactoryInterface class');
            }
            $this->addStoreDataFactory($factory);
        }
        $this->registerServicesFromConfigs();
    }

    public function save(StoreDataHelper $data): void
    {
        if (empty($this->services)) {
            throw new \RuntimeException('No Store Data service is defined');
        }

        foreach ($this->services as $service) {
            $service->save($data);
        }
    }

    public function read(string $key): StoreDataHelper
    {
        if (empty($this->services)) {
            throw new \RuntimeException('No Store Data service is defined');
        }

        $notFoundServices = [];
        $storeDataHelper = null;
        foreach ($this->services as $service) {
            $storeDataHelper = $service->read($key);
            if (null !== $storeDataHelper) {
                break;
            }
            $notFoundServices[] = $service;
        }
        if (null === $storeDataHelper) {
            $storeDataHelper = new StoreDataHelper($key);
        }
        foreach ($notFoundServices as $service) {
            $service->save($storeDataHelper);
        }

        return $storeDataHelper;
    }

    private function addStoreDataFactory(StoreDataFactoryInterface $factory): void
    {
        $this->factories[$factory->getType()] = $factory;
    }

    private function registerServicesFromConfigs(): void
    {
        foreach ($this->storeDataConfigs as $storeDataConfig) {
            if (!isset($this->factories[$storeDataConfig['type']])) {
                $this->logger->warning(\sprintf('Store data factory %s not registered', $storeDataConfig['type']));
                continue;
            }
            $this->services[] = $this->factories[$storeDataConfig['type']]->createService($storeDataConfig);
        }
    }

    public function delete(string $key): void
    {
        foreach ($this->services as $service) {
            $service->delete($key);
        }
    }

    public function gc(): void
    {
        foreach ($this->services as $service) {
            $service->gc();
        }
    }
}
