<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use Symfony\Component\Form\FormInterface;

final class HandleRequest implements HandleRequestInterface
{
    /**
     * @param FormInterface<FormInterface> $form
     */
    public function __construct(
        private readonly FormInterface $form,
        private readonly FormConfig $formConfig,
        private readonly FormData $formData,
        private readonly HandleResponseCollector $responseCollector,
        private readonly SubmissionConfig $submissionConfig,
    ) {
    }

    public function addResponse(HandleResponseInterface $response): void
    {
        $this->responseCollector->addResponse($response);
    }

    public function getClass(): string
    {
        return $this->submissionConfig->getClass();
    }

    /** @return FormInterface<FormInterface> */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getFormData(): FormData
    {
        return $this->formData;
    }

    public function getFormConfig(): FormConfig
    {
        return $this->formConfig;
    }

    public function getEndPoint(): string
    {
        return $this->submissionConfig->getEndpoint();
    }

    public function getMessage(): string
    {
        return $this->submissionConfig->getMessage();
    }

    public function getResponses(): array
    {
        return $this->responseCollector->getResponses();
    }
}
