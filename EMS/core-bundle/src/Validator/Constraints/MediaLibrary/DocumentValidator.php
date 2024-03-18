<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Validator\Constraints\MediaLibrary;

use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryDocument;
use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryService;
use EMS\CoreBundle\EMSCoreBundle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DocumentValidator extends ConstraintValidator
{
    public function __construct(private readonly MediaLibraryService $mediaLibraryService)
    {
    }

    /**
     * @param MediaLibraryDocument $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof MediaLibraryDocument) {
            throw new UnexpectedValueException($value, MediaLibraryDocument::class);
        }

        if (!$constraint instanceof Document) {
            throw new UnexpectedValueException($constraint, Document::class);
        }

        if (!$value->hasName()) {
            return;
        }

        if ($this->mediaLibraryService->count($value->getPath()->getValue(), $value->id) > 0) {
            $this->context
                ->buildViolation('media_library.error.folder_exists')
                ->setTranslationDomain(EMSCoreBundle::TRANS_COMPONENT)
                ->atPath('name')
                ->addViolation();
        }
    }
}
