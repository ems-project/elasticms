<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Admin;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Data\Data;
use EMS\CommonBundle\Common\CoreApi\Endpoint\File\DataExtract;
use EMS\CommonBundle\Common\CoreApi\Endpoint\File\File;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Form\Form;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Meta\Meta;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Search\Search;
use EMS\CommonBundle\Common\CoreApi\Endpoint\User\User;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\MetaInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\UserInterface;
use EMS\CommonBundle\Storage\StorageManager;
use Psr\Log\LoggerInterface;

final class CoreApi implements CoreApiInterface
{
    private readonly File $fileEndpoint;
    private readonly Search $searchEndpoint;
    private readonly DataExtract $dataExtractEndpoint;

    public function __construct(private readonly Client $client, StorageManager $storageManager)
    {
        $this->fileEndpoint = new File($client, $storageManager);
        $this->searchEndpoint = new Search($client, $this->admin());
        $this->dataExtractEndpoint = new DataExtract($client);
    }

    public function authenticate(string $username, string $password): CoreApiInterface
    {
        $response = $this->client->post('/auth-token', [
            'username' => $username,
            'password' => $password,
        ]);

        $authToken = $response->getData()['authToken'] ?? null;

        if (null !== $authToken) {
            $this->setToken($authToken);
        }

        return $this;
    }

    public function queue(int $flushSize): ResponseQueue
    {
        return new ResponseQueue($flushSize);
    }

    public function data(string $contentType): DataInterface
    {
        $versions = $this->admin()->getVersions();

        return new Data($this->client, $contentType, $versions['core'] ?? '1.0.0');
    }

    public function file(): File
    {
        return $this->fileEndpoint;
    }

    public function search(): Search
    {
        return $this->searchEndpoint;
    }

    public function dataExtract(): DataExtract
    {
        return $this->dataExtractEndpoint;
    }

    public function getBaseUrl(): string
    {
        return $this->client->getBaseUrl();
    }

    public function getToken(): string
    {
        return $this->client->getHeader(self::HEADER_TOKEN);
    }

    public function isAuthenticated(): bool
    {
        return $this->client->hasHeader(self::HEADER_TOKEN);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->client->setLogger($logger);
    }

    public function setToken(string $token): void
    {
        $this->client->addHeader(self::HEADER_TOKEN, $token);
    }

    public function test(): bool
    {
        try {
            return $this->client->get('/api/test')->isSuccess();
        } catch (\Throwable) {
            return false;
        }
    }

    public function user(): UserInterface
    {
        return new User($this->client);
    }

    public function admin(): AdminInterface
    {
        return new Admin($this->client);
    }

    public function meta(): MetaInterface
    {
        return new Meta($this->client);
    }

    public function form(): FormInterface
    {
        return new Form($this->client);
    }

    /**
     * @deprecated
     */
    public function hashFile(string $filename): string
    {
        @\trigger_error('CoreApi::hashFile is deprecated use the CorePai/File/File::hashFile', E_USER_DEPRECATED);

        return $this->fileEndpoint->hashFile($filename);
    }

    /**
     * @deprecated
     */
    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int
    {
        @\trigger_error('CoreApi::initUpload is deprecated use the CorePai/File/File::initUpload', E_USER_DEPRECATED);

        return $this->fileEndpoint->initUpload($hash, $size, $filename, $mimetype);
    }

    /**
     * @deprecated
     */
    public function addChunk(string $hash, string $chunk): int
    {
        @\trigger_error('CoreApi::addChunk is deprecated use the CorePai/File/File::addChunk', E_USER_DEPRECATED);

        return $this->fileEndpoint->addChunk($hash, $chunk);
    }
}
