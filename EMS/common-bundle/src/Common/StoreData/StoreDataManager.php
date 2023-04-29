<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData;

use EMS\CommonBundle\Common\StoreData\Factory\StoreDataFactoryInterface;
use Psr\Log\LoggerInterface;

class StoreDataManager
{
    /** @var StoreDataFactoryInterface[] */
    private array $factories = [];

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
    }

    public function read(string $key): StoreDataHelper
    {
        return new StoreDataHelper($key);
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
            \dump($storeDataConfig);
        }
    }
}
