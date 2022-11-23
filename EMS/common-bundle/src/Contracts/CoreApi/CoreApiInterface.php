<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Endpoint\File\DataExtract;
use EMS\CommonBundle\Common\CoreApi\Endpoint\File\File;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Search\Search;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\MetaInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\UserInterface;
use EMS\CommonBundle\Contracts\CoreApi\Exception\BaseUrlNotDefinedExceptionInterface;
use EMS\CommonBundle\Contracts\CoreApi\Exception\NotAuthenticatedExceptionInterface;
use Psr\Log\LoggerInterface;

interface CoreApiInterface
{
    public const HEADER_TOKEN = 'X-Auth-Token';

    /**
     * @throws CoreApiExceptionInterface
     * @throws NotAuthenticatedExceptionInterface
     */
    public function authenticate(string $username, string $password): CoreApiInterface;

    public function data(string $contentType): DataInterface;

    public function file(): File;

    public function search(): Search;

    public function dataExtract(): DataExtract;

    /**
     * @throws BaseUrlNotDefinedExceptionInterface
     */
    public function getBaseUrl(): string;

    public function getToken(): string;

    public function isAuthenticated(): bool;

    public function setLogger(LoggerInterface $logger): void;

    public function setToken(string $token): void;

    /**
     * @throws BaseUrlNotDefinedExceptionInterface
     * @throws NotAuthenticatedExceptionInterface
     */
    public function test(): bool;

    public function user(): UserInterface;

    public function admin(): AdminInterface;

    public function meta(): MetaInterface;

    public function form(): FormInterface;

    /**
     * @deprecated
     */
    public function hashFile(string $filename): string;

    /**
     * @deprecated
     */
    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int;

    /**
     * @deprecated
     */
    public function addChunk(string $hash, string $chunk): int;
}
