<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

final class StoreRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function save(StoreDataHelper $data): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->isMethodSafe()) {
            throw new \RuntimeException(\sprintf('The safe method %s is not allowed when saving data', $request->getMethod()));
        }
    }

    public function read(string $key): StoreDataHelper
    {
        return new StoreDataHelper();
    }
}
