<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\HandleRequest;
use EMS\FormBundle\Submission\HandleResponseCollector;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Tests\Functional\AbstractFunctionalTest;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractHandlerTest extends AbstractFunctionalTest
{
    protected FormFactoryInterface $formFactory;
    protected FormConfig $formConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $this->formConfig = new FormConfig('1', 'nl', 'test_test');
    }

    abstract protected function getHandler(): AbstractHandler;

    protected function handle(FormInterface $form, string $endpoint, string $message): HandleResponseInterface
    {
        $handler = $this->getHandler();
        $submissionConfig = new SubmissionConfig($handler::class, $endpoint, $message);

        $handleRequest = new HandleRequest($form, $this->formConfig, new HandleResponseCollector(), $submissionConfig);

        return $handler->handle($handleRequest);
    }

    protected function createForm(array $data = []): FormInterface
    {
        if (null == $data) {
            $data = [
                'first_name' => 'testFirstName',
                'last_name' => 'testLastName',
                'email' => 'user1@test.test',
            ];
        }

        return $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
            ->add('info', TextType::class)
            ->add('email', EmailType::class)
            ->getForm();
    }

    protected function createFormUploadFiles(): FormInterface
    {
        $data = [
            'info' => 'Uploaded 2 files',
            'files' => [
                new UploadedFile(__DIR__.'/../fixtures/files/attachment.txt', 'attachment.txt', 'text/plain'),
                new UploadedFile(__DIR__.'/../fixtures/files/attachment2.txt', 'attachment2.txt', 'text/plain'),
            ],
        ];

        return $this->formFactory->createBuilder(FormType::class, $data, [])
            ->add('info', TextType::class)
            ->add('files', FileType::class, ['multiple' => true])
            ->getForm();
    }
}
