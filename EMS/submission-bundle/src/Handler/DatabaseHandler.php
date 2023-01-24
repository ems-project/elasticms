<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Entity\FormSubmission;
use EMS\SubmissionBundle\Request\DatabaseRequest;
use EMS\SubmissionBundle\Response\DatabaseHandleResponse;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Twig\TwigRenderer;

final class DatabaseHandler extends AbstractHandler
{
    public function __construct(private readonly Registry $registry, private readonly TwigRenderer $twigRenderer, private readonly ResponseTransformer $responseTransformer)
    {
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $databaseRecord = $this->twigRenderer->renderMessageBlockJSON($handleRequest, 'databaseRecord');

            $request = new DatabaseRequest($databaseRecord);
            $formSubmission = new FormSubmission($request);

            $em = $this->registry->getManager();
            $em->persist($formSubmission);
            $em->flush();

            $handleResponse = new DatabaseHandleResponse($formSubmission);

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Throwable $exception) {
            return new FailedHandleResponse($exception);
        }
    }
}
