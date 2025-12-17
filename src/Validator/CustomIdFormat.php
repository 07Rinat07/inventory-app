<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class CustomIdFormat extends Constraint
{
    public string $message = 'Custom ID does not match inventory format.';

    /**
     * Явно указываем валидатор
     * Symfony автоматически найдёт класс CustomIdFormatValidator
     */
    public function validatedBy(): string
    {
        return CustomIdFormatValidator::class;
    }
}
