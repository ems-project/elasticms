<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Twig\AssetRuntime;
use EMS\Helpers\Html\Headers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AssetController extends AbstractController
{
    public function __construct(private readonly AssetRuntime $assetRuntime, private readonly string $projectDir)
    {
    }

    public function proxyToEnvironmentAlias(string $requestPath, string $alias): Response
    {
        @\trigger_error(\sprintf('The route entry %s::proxyToEnvironmentAlias is deprecated, please use EMS\CommonBundle\Controller\FileController::assetInArchive', self::class), E_USER_DEPRECATED);
        $target = \implode(DIRECTORY_SEPARATOR, [
            'bundles',
            $alias,
        ]);

        return $this->proxy($requestPath, $target);
    }

    public function proxyToZipArchive(string $requestPath, string $hash): Response
    {
        @\trigger_error(\sprintf('The route entry %s::proxyToZipArchive is deprecated, please use EMS\CommonBundle\Controller\FileController::assetInArchive', self::class), E_USER_DEPRECATED);
        $saveDir = \implode(DIRECTORY_SEPARATOR, [
            $this->projectDir,
            'public',
            'bundles',
            $hash,
        ]);
        $this->assetRuntime->unzip($hash, $saveDir);

        $target = \implode(DIRECTORY_SEPARATOR, [
            'bundles',
            $hash,
        ]);

        return $this->proxy($requestPath, $target);
    }

    public function proxy(string $requestPath, string $target): Response
    {
        @\trigger_error(\sprintf('The route entry %s::proxy is deprecated, please use EMS\CommonBundle\Controller\FileController::assetInArchive', self::class), E_USER_DEPRECATED);
        $file = \implode(DIRECTORY_SEPARATOR, [
            $this->projectDir,
            'public',
            $target,
            $requestPath,
        ]);

        if (!\file_exists($file)) {
            throw new NotFoundHttpException(\sprintf('File %s not found', $file));
        }
        $response = new BinaryFileResponse($file);
        $response->headers->set(Headers::CONTENT_TYPE, MimeTypeHelper::getInstance()->guessMimeType($file));
        $response->headers->set('X-Proxy-Target-Base-Url', $target);

        return $response;
    }
}
