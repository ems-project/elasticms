<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use Symfony\Component\Form\FormInterface;

final readonly class HandleRequest implements HandleRequestInterface
{
    /**
     * @param FormInterface<FormInterface> $form
     */
    public function __construct(
        private FormInterface $form,
        private FormConfig $formConfig,
        private FormData $formData,
        private HandleResponseCollector $responseCollector,
        private SubmissionConfig $submissionConfig,
    ) {
    }

    #[\Override]
    public function addResponse(HandleResponseInterface $response): void
    {
        $this->responseCollector->addResponse($response);
    }

    #[\Override]
    public function getClass(): string
    {
        return $this->submissionConfig->getClass();
    }

    /** @return FormInterface<FormInterface> */
    #[\Override]
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    #[\Override]
    public function getFormData(): FormData
    {
        return $this->formData;
    }

    #[\Override]
    public function getFormConfig(): FormConfig
    {
        return $this->formConfig;
    }

    #[\Override]
    public function getEndPoint(): string
    {
        return $this->submissionConfig->getEndpoint();
    }

    #[\Override]
    public function getMessage(): string
    {
        return $this->submissionConfig->getMessage();
    }

    #[\Override]
    public function getResponses(): array
    {
        return $this->responseCollector->getResponses();
    }
}
