<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Controller;

use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\Helpers\File\File;
use EMS\Helpers\Html\Headers;
use EMS\Helpers\Standard\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends AbstractController
{
    public function __construct(
        private readonly Processor $processor
    ) {
    }

    public function asset(Request $request, string $hash, string $hash_config, string $filename): Response
    {
        $this->closeSession($request);

        return $this->processor->getResponse($request, $hash, $hash_config, $filename, true);
    }

    /**
     * @param mixed[] $fileField
     * @param mixed[] $configArray
     */
    public function resolveAsset(Request $request, array $fileField, array $configArray = []): Response
    {
        $this->closeSession($request);

        return $this->processor->resolveAndGetResponse($request, $fileField, $configArray);
    }

    public function generateLocalImage(Request $request, string $filename, string $config = '[]'): Response
    {
        $this->closeSession($request);
        $options = Json::decode($config);
        $generatedFile = $this->processor->generateLocalImage($filename, $options, $request->isNoCache());
        $response = new StreamedResponse(function () use ($generatedFile) {
            if ($generatedFile->isSeekable() && $generatedFile->tell() > 0) {
                $generatedFile->rewind();
            }

            while (!$generatedFile->eof()) {
                echo $generatedFile->read(File::DEFAULT_CHUNK_SIZE);
            }
            $generatedFile->close();
        });
        $config = $this->processor->localFileConfig($filename, $options);
        $response->headers->add([
            Headers::CONTENT_DISPOSITION => $config->getDisposition().'; '.HeaderUtils::toString(['filename' => \basename($filename)], ';'),
            Headers::CONTENT_TYPE => $config->getMimeType(),
        ]);
        $response->setCache([
            'etag' => \hash('sha1', \sprintf('Local Image from %s and config: %s', $filename, Json::encode($config))),
            'max_age' => 3600,
            's_maxage' => 36000,
            'public' => true,
            'private' => false,
        ]);

        return $response;
    }

    public function assetInArchive(Request $request, string $hash, string $path, int $maxAge = 604800, bool $extract = true, ?string $indexResource = null, ?string $notFoundTemplate = null): Response
    {
        $this->closeSession($request);

        try {
            return $this->processor->getResponseFromArchive($request, $hash, $path, $maxAge, $extract, $indexResource);
        } catch (NotFoundHttpException $notFoundHttpException) {
            if (null === $notFoundTemplate) {
                throw $notFoundHttpException;
            }
        }

        try {
            $response = $this->render($notFoundTemplate, [
                'error' => $notFoundHttpException,
                'hash' => $hash,
                'path' => $path,
                'maxAge' => $maxAge,
                'extract' => $extract,
                'indexResource' => $indexResource,
            ]);
            $response->setStatusCode(404);

            return $response;
        } catch (\Throwable $throwable) {
            throw $throwable->getPrevious() instanceof HttpException ? $throwable->getPrevious() : $throwable;
        }
    }

    private function closeSession(Request $request): void
    {
        if (!$request->hasSession(true)) {
            return;
        }

        $session = $request->getSession();
        if ($session->isStarted()) {
            $session->save();
        }
    }
}
