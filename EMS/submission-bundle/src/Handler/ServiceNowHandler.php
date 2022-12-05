<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\ServiceNowRequest;
use EMS\SubmissionBundle\Response\ServiceNowHandleResponse;
use EMS\SubmissionBundle\Twig\TwigRenderer;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ServiceNowHandler extends AbstractHandler
{
    public function __construct(private readonly HttpClientInterface $client, private readonly int $timeout, private readonly TwigRenderer $twigRenderer)
    {
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $message = $this->twigRenderer->renderMessageJSON($handleRequest);

            $serviceNowRequest = new ServiceNowRequest($endpoint, $message);

            $response = $this->client->request('POST', $serviceNowRequest->getBodyEndpoint(), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => $serviceNowRequest->getBasicAuth(),
                ],
                'body' => $serviceNowRequest->getBody(),
                'timeout' => $this->timeout,
            ]);

            $serviceNowResponse = new ServiceNowHandleResponse($response->getContent());

            foreach ($serviceNowRequest->getAttachments() as $attachment) {
                $this->postAttachment($serviceNowResponse, $serviceNowRequest, $attachment);
            }

            return $serviceNowResponse;
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }

    /**
     * @param array<string> $attachment
     */
    private function postAttachment(ServiceNowHandleResponse $response, ServiceNowRequest $request, array $attachment): void
    {
        try {
            $file = new SplFileInfo($attachment['pathname'], '', '');

            $this->client->request('POST', $request->getAttachmentEndpoint(), [
                'query' => [
                    'file_name' => $attachment['originalName'],
                    'table_name' => $request->getAttachmentTable(),
                    'table_sys_id' => $response->getResultProperty('sys_id'),
                ],
                'headers' => [
                    'Content-Type' => $attachment['mimeType'],
                    'Authorization' => $request->getBasicAuth(),
                ],
                'body' => $file->getContents(),
            ]);
        } catch (\Exception $exception) {
            throw new \Exception(\sprintf('Attachment submission failed: %s', $exception->getMessage()));
        }
    }
}
