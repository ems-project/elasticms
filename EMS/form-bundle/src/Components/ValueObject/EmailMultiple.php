<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\ValueObject;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

final readonly class EmailMultiple
{
    public function __construct(private string $emails)
    {
        if (!$this->validate()) {
            throw new \Exception(\sprintf('At least one email is not valid: %s', $this->emails));
        }
    }

    private function validate(): bool
    {
        $validator = Validation::createValidator();
        $emails = \explode(',', $this->emails);
        foreach ($emails as $email) {
            $errors = $validator->validate(\trim($email), new Email(['mode' => Email::VALIDATION_MODE_HTML5]));
            if (0 < \count($errors)) {
                return false;
            }
        }

        return true;
    }
}
