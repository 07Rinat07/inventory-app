<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CustomIdFormat extends Constraint
{
    public string $message = 'Custom ID does not match inventory format';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
