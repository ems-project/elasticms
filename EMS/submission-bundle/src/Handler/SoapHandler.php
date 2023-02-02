<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\SoapRequest;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Response\SoapHandleResponse;
use EMS\SubmissionBundle\SoapClient;
use EMS\SubmissionBundle\Twig\TwigRenderer;

final class SoapHandler extends AbstractHandler
{
    public function __construct(private readonly TwigRenderer $twigRenderer, private readonly ResponseTransformer $responseTransformer)
    {
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $arguments = $this->twigRenderer->renderMessageBlockJSON($handleRequest, 'arguments');

            $soapRequest = new SoapRequest($endpoint);
            $soapClient = new SoapClient($soapRequest->getWsdl(), $soapRequest->getOptions());

            $soapResponse = $soapClient->call($soapRequest->getOperation(), $arguments);
            $handleResponse = new SoapHandleResponse($soapRequest, $soapResponse);

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Throwable $exception) {
            return new FailedHandleResponse($exception);
        }
    }
}
