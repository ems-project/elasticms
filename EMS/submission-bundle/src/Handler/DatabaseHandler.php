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
    /** @var Registry */
    private $registry;
    /** @var TwigRenderer */
    private $twigRenderer;
    /** @var ResponseTransformer */
    private $responseTransformer;

    public function __construct(
        Registry $registry,
        TwigRenderer $twigRenderer,
        ResponseTransformer $responseTransformer
    ) {
        $this->registry = $registry;
        $this->twigRenderer = $twigRenderer;
        $this->responseTransformer = $responseTransformer;
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
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
