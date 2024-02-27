<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Session;

use EMS\CommonBundle\Common\StoreData\StoreDataManager;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

class StoreDataSessionHandler extends AbstractSessionHandler
{
    private const SESSION = '[_ems_session]';
    private bool $gcCalled = false;

    public function __construct(private readonly StoreDataManager $storeDataManager)
    {
    }

    protected function doRead(string $sessionId): string
    {
        return (string) $this->storeDataManager->read($sessionId)->get(self::SESSION);
    }

    protected function doWrite(string $sessionId, string $data): bool
    {
        $dataHelper = $this->storeDataManager->read($sessionId);
        $dataHelper->set(self::SESSION, $data);
        $this->storeDataManager->save($dataHelper);

        return true;
    }

    protected function doDestroy(string $sessionId): bool
    {
        $this->storeDataManager->delete($sessionId);

        return true;
    }

    public function close(): bool
    {
        if ($this->gcCalled) {
            $this->storeDataManager->gc();
        }

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $this->gcCalled = true;

        return 0;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        $this->doWrite($id, $data);

        return true;
    }
}
