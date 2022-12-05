<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\HttpRequest;
use EMS\SubmissionBundle\Response\HttpHandleResponse;
use EMS\SubmissionBundle\Response\NoHttpRequest;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Twig\TwigRenderer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpHandler extends AbstractHandler
{
    public function __construct(private readonly HttpClientInterface $client, private readonly TwigRenderer $twigRenderer, private readonly ResponseTransformer $responseTransformer)
    {
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $body = $this->twigRenderer->renderMessageBlock($handleRequest, 'requestBody') ?? '';

            $httpRequest = new HttpRequest($endpoint, $body);
            if (null !== $httpRequest->getIgnoreBodyValue() && $body === $httpRequest->getIgnoreBodyValue()) {
                $handleResponse = new NoHttpRequest();
            } else {
                $httpResponse = $this->client->request($httpRequest->getMethod(), $httpRequest->getUrl(), $httpRequest->getHttpOptions());
                $httpResponseContent = $httpResponse->getContent(true);
                $handleResponse = new HttpHandleResponse($httpResponse, $httpResponseContent);
            }

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
