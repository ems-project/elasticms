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

final readonly class CoreApi implements CoreApiInterface
{
    private File $fileEndpoint;
    private Search $searchEndpoint;
    private DataExtract $dataExtractEndpoint;

    public function __construct(private Client $client, StorageManager $storageManager)
    {
        $this->fileEndpoint = new File($client, $storageManager);
        $this->searchEndpoint = new Search($client, $this->admin());
        $this->dataExtractEndpoint = new DataExtract($client);
    }

    #[\Override]
    public function authenticate(string $username, string $password, ?string $baseUrl = null): CoreApiInterface
    {
        $this->setBaseUrl($baseUrl);

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

    #[\Override]
    public function queue(int $flushSize): ResponseQueue
    {
        return new ResponseQueue($flushSize);
    }

    #[\Override]
    public function data(string $contentType): DataInterface
    {
        $versions = $this->admin()->getVersions();

        return new Data($this->client, $contentType, $versions['core'] ?? '1.0.0');
    }

    #[\Override]
    public function file(): File
    {
        return $this->fileEndpoint;
    }

    #[\Override]
    public function search(): Search
    {
        return $this->searchEndpoint;
    }

    #[\Override]
    public function dataExtract(): DataExtract
    {
        return $this->dataExtractEndpoint;
    }

    #[\Override]
    public function getBaseUrl(): string
    {
        return $this->client->getBaseUrl();
    }

    #[\Override]
    public function setBaseUrl(?string $baseUrl = null): self
    {
        if (null !== $baseUrl) {
            $this->client->setBaseUrl($baseUrl);
        }

        return $this;
    }

    #[\Override]
    public function getToken(): string
    {
        return $this->client->getHeader(self::HEADER_TOKEN);
    }

    #[\Override]
    public function isAuthenticated(): bool
    {
        return $this->client->hasHeader(self::HEADER_TOKEN);
    }

    #[\Override]
    public function setLogger(LoggerInterface $logger): self
    {
        $this->client->setLogger($logger);

        return $this;
    }

    #[\Override]
    public function setToken(string $token): self
    {
        $this->client->addHeader(self::HEADER_TOKEN, $token);

        return $this;
    }

    #[\Override]
    public function test(): bool
    {
        try {
            return $this->client->get('/api/test')->isSuccess();
        } catch (\Throwable) {
            return false;
        }
    }

    #[\Override]
    public function user(): UserInterface
    {
        return new User($this->client);
    }

    #[\Override]
    public function admin(): AdminInterface
    {
        return new Admin($this->client);
    }

    #[\Override]
    public function meta(): MetaInterface
    {
        return new Meta($this->client);
    }

    #[\Override]
    public function form(): FormInterface
    {
        return new Form($this->client);
    }

    /**
     * @deprecated
     */
    #[\Override]
    public function hashFile(string $filename): string
    {
        @\trigger_error('CoreApi::hashFile is deprecated use the CorePai/File/File::hashFile', E_USER_DEPRECATED);

        return $this->fileEndpoint->hashFile($filename);
    }

    /**
     * @deprecated
     */
    #[\Override]
    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int
    {
        @\trigger_error('CoreApi::initUpload is deprecated use the CorePai/File/File::initUpload', E_USER_DEPRECATED);

        return $this->fileEndpoint->initUpload($hash, $size, $filename, $mimetype);
    }

    /**
     * @deprecated
     */
    #[\Override]
    public function addChunk(string $hash, string $chunk): int
    {
        @\trigger_error('CoreApi::addChunk is deprecated use the CorePai/File/File::addChunk', E_USER_DEPRECATED);

        return $this->fileEndpoint->addChunk($hash, $chunk);
    }
}
