<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Spreadsheet;

use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetValidationInterface;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class SpreadsheetValidation implements SpreadsheetValidationInterface
{
    private string $type;
    private string $formula;
    private bool $allowBlank;
    private string $prompt;
    private string $error;
    private bool $showInput;
    private bool $showError;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $options = $this->resolveOptions($options);
        $this->type = $options[self::TYPE];
        $this->formula = $options[self::FORMULA];
        $this->allowBlank = $options[self::ALLOW_BLANK];
        $this->prompt = $options[self::PROMPT];
        $this->error = $options[self::ERROR];
        $this->showInput = $options[self::SHOW_INPUT];
        $this->showError = $options[self::SHOW_ERROR];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{type: string, formula: string, allow_blank: bool, show_input: bool, show_error: bool, prompt_title: string, error_title: string}
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([self::FORMULA])
            ->setDefaults([
                self::TYPE => 'list',
                self::ALLOW_BLANK => true,
                self::SHOW_INPUT => true,
                self::SHOW_ERROR => true,
                self::PROMPT => self::PROMPT_TEXT,
                self::ERROR => self::ERROR_TEXT,
            ])
            ->setAllowedTypes(self::TYPE, ['string'])
            ->setAllowedTypes(self::FORMULA, ['string'])
            ->setAllowedTypes(self::ALLOW_BLANK, ['bool'])
            ->setAllowedTypes(self::SHOW_INPUT, ['bool'])
            ->setAllowedTypes(self::SHOW_ERROR, ['bool'])
            ->setAllowedTypes(self::PROMPT, ['string'])
            ->setAllowedTypes(self::ERROR, ['string'])
        ;
        /** @var array{type: string, formula: string, allow_blank: bool, show_input: bool, show_error: bool, prompt_title: string, error_title: string} $resolvedParameter */
        $resolvedParameter = $resolver->resolve($options);

        return $resolvedParameter;
    }

    public function addValidation(DataValidation $cellValidation): DataValidation
    {
        if ('list' == $this->type) {
            $cellValidation->setType(DataValidation::TYPE_LIST)
                ->setAllowBlank($this->allowBlank)
                ->setShowDropDown(true)
                ->setShowInputMessage($this->showInput)
                ->setShowErrorMessage($this->showError)
                ->setPrompt($this->prompt)
                ->setError($this->error)
                ->setFormula1('"'.$this->formula.'"');
        }

        return $cellValidation;
    }
}
