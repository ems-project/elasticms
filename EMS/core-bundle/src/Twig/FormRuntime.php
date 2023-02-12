<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig;

use EMS\CoreBundle\Core\Form\FormManager;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\DataField;
use EMS\CoreBundle\Entity\Form;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Form\Form\RevisionType;
use EMS\CoreBundle\Service\DataService;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

class FormRuntime
{
    public function __construct(protected FormManager $formManager, protected FormFactory $formFactory, protected DataService $dataService)
    {
    }

    /**
     * @param mixed[] $rawData
     */
    public function getFormByName(string $name, array $rawData = []): ?FormInterface
    {
        $formEntity = $this->formManager->getByItemName($name);
        if (null === $formEntity) {
            return null;
        }

        if (!$formEntity instanceof Form) {
            throw new \RuntimeException('Unexpected non-Form entity');
        }
        $fakeContentType = new ContentType();
        $fakeContentType->setFieldType($formEntity->getFieldType());
        $fakeRevision = new Revision();
        $fakeRevision->setContentType($fakeContentType);
        $fakeRevision->setRawData($rawData);
        $form = $this->formFactory->create(RevisionType::class, $fakeRevision, ['raw_data' => $rawData]);

        return $form->get('data');
    }

    public function getDataField(FormInterface $form): DataField
    {
        return $this->dataService->getDataFieldsStructure($form);
    }
}
