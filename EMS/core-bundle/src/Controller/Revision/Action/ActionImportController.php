<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Revision\Action;

use EMS\CoreBundle\Core\UI\AjaxService;
use EMS\CoreBundle\Repository\TemplateRepository;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

class ActionImportController
{
    public function __construct(
        private readonly TemplateRepository $templateRepository,
        private readonly AjaxService $ajax,
        private readonly FormFactory $formFactory,
    ) {
    }

    public function __invoke(Request $request, int $templateId, string $ouuid): Response
    {
        $action = $this->templateRepository->getById($templateId);
        $modal = $this->ajax->newAjaxModel('@EMSCore/action/modal_import.html.twig');

        $form = $this->formFactory->createBuilder(FormType::class, [])
            ->add('import_file', FileType::class, ['constraints' => [new NotBlank()]])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('import_file')->getData();

            $test = 1;
        }

        return $modal
            ->setIcon($action->getIcon())
            ->setTitleRaw($action->getLabel())
            ->setBody('body', ['form' => $form->createView()])
            ->setFooter('footer')
            ->getResponse();
    }
}
