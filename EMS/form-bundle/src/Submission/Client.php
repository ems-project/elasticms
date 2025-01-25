<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestInterface;
use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestManagerInterface;
use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\SubmissionBundle\Exception\SkipSubmissionException;
use Symfony\Component\Form\FormInterface;

class Client
{
    private readonly ClientRequestInterface $clientRequest;

    /** @param \Traversable<AbstractHandler> $handlers */
    public function __construct(ClientRequestManagerInterface $clientRequestManager, private readonly \Traversable $handlers)
    {
        $this->clientRequest = $clientRequestManager->getDefault();
    }

    /**
     * @param FormInterface<mixed> $form
     *
     * @return array<string, array<array<string, string>>|string>
     */
    public function submit(FormInterface $form, string $ouuid): array
    {
        /** @var FormConfig $formConfig */
        $formConfig = $form->getConfig()->getOption('config');
        $this->loadSubmissions($formConfig);

        $responseCollector = new HandleResponseCollector();

        $formData = new FormData($formConfig, $form);
        foreach ($formConfig->getSubmissions() as $submissionConfig) {
            if (!$submissionConfig instanceof SubmissionConfig) {
                throw new \RuntimeException('Unexpected not loaded submissions');
            }
            $handleRequest = new HandleRequest($form, $formConfig, $formData, $responseCollector, $submissionConfig);
            $handler = $this->getHandler($handleRequest);

            if (null === $handler) {
                continue;
            }

            try {
                $handleResponse = $handler->handle($handleRequest);
                $handleRequest->addResponse($handleResponse);

                if ($handleResponse instanceof AbortHandleResponse) {
                    break;
                }
            } catch (SkipSubmissionException) {
            }
        }

        return [
            'instruction' => 'submitted',
            'ouuid' => $ouuid,
            'response' => $responseCollector->toJson(),
            'summaries' => $responseCollector->getSummaries(),
        ];
    }

    private function getHandler(HandleRequestInterface $handleRequest): ?AbstractHandler
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof AbstractHandler && $handler->canHandle($handleRequest->getClass())) {
                return $handler;
            }
        }

        return null;
    }

    private function loadSubmissions(FormConfig $config): void
    {
        $emsLinkSubmissions = $config->getSubmissions();

        $submissions = [];

        foreach ($emsLinkSubmissions as $emsLinkSubmission) {
            if ($emsLinkSubmission instanceof SubmissionConfig) {
                $submissions[] = $emsLinkSubmission; // This is here to please phpstan, caused because we use the $config->submissions property for initialisation and the end result!
                continue;
            }

            $submission = $this->clientRequest->getByEmsKey($emsLinkSubmission, []);
            if (false === $submission) {
                continue;
            }

            $submissions[] = new SubmissionConfig($submission['_source']['type'], $submission['_source']['endpoint'], $submission['_source']['message']);
        }

        $config->setSubmissions($submissions);
    }
}
