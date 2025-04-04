<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form;

enum EmschFormBlock: string
{
    case SUCCESS_REDIRECT = 'emschFormSuccessRedirect';
    case DATA = 'emschFormData';
    case CONFIG = 'emschFormConfig';
    case VALIDATE = 'emschFormValidate';
    case VIEW = 'emschFormView';
}
