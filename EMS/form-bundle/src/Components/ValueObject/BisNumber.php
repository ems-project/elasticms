<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\ValueObject;

class BisNumber extends RrNumber
{
    /**
     * BIS-nummer.
     *
     * Dit nummer wordt uitgereikt door de KSZ en bestaat uit 11 cijfers.
     * Het heeft dezelfde opbouw als een rijksregisternummer maar de geboortemaand wordt vermeerderd met 40 indien het geslacht
     * van de persoon gekend is op het moment van de toekenning van het nummer, of vermeerderd met 20 indien het geslacht
     * van de persoon niet gekend is op het moment van de toekenning.
     *
     * valid bisnumbers 00/00/00-000.43 and 00/00/00-000.86
     */
    public function __construct(string $number)
    {
        try {
            parent::__construct($number);
        } catch (\Exception) {
            throw new \Exception(\sprintf('invalid bis data: %s', $number));
        }
    }

    #[\Override]
    protected function validate(): bool
    {
        $baseInt = (int) $this->base;
        $baseModifier = 2_000_000;

        // augment once for person with unknown sex
        $this->base = \sprintf('%d', $baseInt + $baseModifier);
        if (parent::validate()) {
            return true;
        }

        // augment twice for person with known sex
        $this->base = \sprintf('%d', $baseInt + $baseModifier + $baseModifier);
        /* @phpstan-ignore-next-line */
        if (parent::validate()) {
            return true;
        }

        return false;
    }
}
