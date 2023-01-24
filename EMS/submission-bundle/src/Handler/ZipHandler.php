<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\ZipRequest;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Response\ZipHandleResponse;
use EMS\SubmissionBundle\Twig\TwigRenderer;
use Symfony\Component\Filesystem\Filesystem;

final class ZipHandler extends AbstractHandler
{
    public function __construct(private readonly TwigRenderer $twigRenderer, private readonly ResponseTransformer $responseTransformer)
    {
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        $filesystem = new Filesystem();

        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $files = $this->twigRenderer->renderMessageBlockJSON($handleRequest, 'files');

            $zipRequest = new ZipRequest($endpoint, $files);
            $tempFile = $filesystem->tempnam(\sys_get_temp_dir(), 'emss');

            $zip = new \ZipArchive();
            $zip->open($tempFile, \ZipArchive::CREATE);

            foreach ($zipRequest->getFiles() as $file) {
                $zip->addFromString($file['path'], $file['contents']);
            }

            $zip->close();

            $zipContent = \file_get_contents($tempFile);
            $handleResponse = new ZipHandleResponse($zipRequest, false === $zipContent ? '' : $zipContent);

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Throwable $exception) {
            return new FailedHandleResponse($exception);
        } finally {
            if (isset($tempFile)) {
                $filesystem->remove($tempFile);
            }
        }
    }
}
