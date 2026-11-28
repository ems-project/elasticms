<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\CommonBundle\Common\StoreData\StoreDataManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;

final readonly class StoreDataExtension
{
    public function __construct(
        private RequestStack $requestStack,
        private StoreDataManager $storeDataManager,
    ) {
    }

    #[AsTwigFunction(name: 'ems_store_delete')]
    public function delete(string $key): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->isMethodSafe()) {
            throw new \RuntimeException(\sprintf('The safe method %s is not allowed when saving data', $request->getMethod()));
        }

        $this->storeDataManager->delete($key);
    }

    #[AsTwigFunction(name: 'ems_store_read')]
    public function read(string $key): StoreDataHelper
    {
        return $this->storeDataManager->read($key);
    }

    #[AsTwigFunction(name: 'ems_store_save')]
    public function save(StoreDataHelper $data): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->isMethodSafe()) {
            throw new \RuntimeException(\sprintf('The safe method %s is not allowed when saving data', $request->getMethod()));
        }

        $this->storeDataManager->save($data);
    }
}
