<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\Helpers\File\TempFile;
use EMS\SubmissionBundle\Request\ZipRequest;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Response\ZipHandleResponse;
use EMS\SubmissionBundle\Twig\TwigRenderer;

final class ZipHandler extends AbstractHandler
{
    public function __construct(private readonly TwigRenderer $twigRenderer, private readonly ResponseTransformer $responseTransformer)
    {
    }

    #[\Override]
    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $files = $this->twigRenderer->renderMessageBlockJSON($handleRequest, 'files');

            $zipRequest = new ZipRequest($endpoint, $files);
            $tempFile = TempFile::create();
            $zip = new \ZipArchive();
            $zip->open($tempFile->path, \ZipArchive::OVERWRITE);

            foreach ($zipRequest->getFiles() as $file) {
                $zip->addFromString($file['path'], $file['contents']);
            }

            $zip->close();

            if (!$tempFile->exists()) {
                throw new \RuntimeException('File not found');
            }

            $handleResponse = new ZipHandleResponse($zipRequest, $tempFile->getContents());

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Throwable $exception) {
            return new FailedHandleResponse($exception);
        }
    }
}
