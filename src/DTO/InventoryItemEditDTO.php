<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class InventoryItemEditDTO
{
    #[Assert\NotBlank]
    public string $customId = '';
}
