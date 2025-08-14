<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge\Core;

use EMS\CommonBundle\Common\Bridge\Core\CoreBridgeResponse;
use EMS\CommonBundle\Common\Bridge\Core\CoreBridgeTrait;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreFileBridgeInterface;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Exception\NotFoundException;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\FileService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class CoreFileServiceBridge implements CoreFileBridgeInterface
{
    use CoreBridgeTrait;
    
    public function __construct(
        private FileService $fileService,
        private string $user,
    )
    {
    }

    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int
    {
        $algo = $this->fileService->getAlgo();
        
        return $this->fileService
            ->initUploadFile($hash, $size, $filename, $mimetype, $this->user, $algo)
            ->getUploaded();
    }

    public function addChunk(string $hash, string $chunk): int
    {
        return $this->fileService->addChunk($hash, $chunk, $this->user)->getUploaded();
    }
}
