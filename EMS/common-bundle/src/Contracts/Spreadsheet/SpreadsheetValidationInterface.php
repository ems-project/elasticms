<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Spreadsheet;

interface SpreadsheetValidationInterface
{
    public const TYPE = 'type';
    public const FORMULA = 'formula';
    public const ALLOW_BLANK = 'allow_blank';
    public const PROMPT = 'prompt_title';
    public const ERROR = 'error_title';
    public const SHOW_INPUT = 'show_input';
    public const SHOW_ERROR = 'show_error';

    public const ERROR_TEXT = "This value doesn't match the data validation restrictions defined for this cell";
    public const PROMPT_TEXT = 'Chose a value from the list';
}
