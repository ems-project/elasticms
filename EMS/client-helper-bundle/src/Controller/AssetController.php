<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\CommonBundle\Twig\AssetRuntime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AssetController extends AbstractController
{
    private string $projectDir;
    private AssetRuntime $assetRuntime;

    public function __construct(AssetRuntime $assetRuntime, string $projectDir)
    {
        $this->assetRuntime = $assetRuntime;
        $this->projectDir = $projectDir;
    }

    public function proxyToEnvironmentAlias(string $requestPath, string $alias): Response
    {
        $target = \implode(DIRECTORY_SEPARATOR, [
            'bundles',
            $alias,
        ]);

        return $this->proxy($requestPath, $target);
    }

    public function proxyToZipArchive(string $requestPath, string $hash): Response
    {
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
        $this->fixGuessedMimeType($response, $file);
        $response->headers->set('X-Proxy-Target-Base-Url', $target);

        return $response;
    }

    private function fixGuessedMimeType(BinaryFileResponse $response, string $file): void
    {
        $exploded = \explode('.', $file);
        $extension = \end($exploded);

        switch ($extension) {
            case 'css':
                $response->headers->set('Content-Type', 'text/css');
                break;
            case 'svg':
                $response->headers->set('Content-Type', 'image/svg+xml');
                break;
        }
    }
}
