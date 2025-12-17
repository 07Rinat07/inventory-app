<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CustomIdFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomIdFormat) {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        // Простая и понятная валидация формата Custom ID
        if (!preg_match('/^[A-Z0-9\-]+$/', $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
