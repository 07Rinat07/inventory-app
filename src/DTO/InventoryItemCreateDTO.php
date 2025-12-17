<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator\CustomIdFormat;
use Symfony\Component\Validator\Constraints as Assert;

final class InventoryItemCreateDTO
{
    /**
     * Custom ID, вводимый пользователем.
     *
     * Проверки:
     *  - не пустой
     *  - базовая длина (защита от мусора)
     *  - соответствие шаблону инвентаря (CustomIdFormat)
     */
    #[Assert\NotBlank(message: 'Custom ID is required.')]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: 'Custom ID cannot be empty.',
        maxMessage: 'Custom ID is too long.'
    )]
    #[CustomIdFormat]
    public string $customId = '';
}
