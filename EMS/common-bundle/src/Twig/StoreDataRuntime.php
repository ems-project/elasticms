<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\CommonBundle\Common\StoreData\StoreDataManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

final class StoreDataRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly StoreDataManager $storeDataManager,
    ) {
    }

    public function save(StoreDataHelper $data): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->isMethodSafe()) {
            throw new \RuntimeException(\sprintf('The safe method %s is not allowed when saving data', $request->getMethod()));
        }

        $this->storeDataManager->save($data);
    }

    public function delete(string $key): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->isMethodSafe()) {
            throw new \RuntimeException(\sprintf('The safe method %s is not allowed when saving data', $request->getMethod()));
        }

        $this->storeDataManager->delete($key);
    }

    public function read(string $key): StoreDataHelper
    {
        return $this->storeDataManager->read($key);
    }
}
