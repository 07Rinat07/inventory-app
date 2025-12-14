<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator\CustomIdFormat;
use Symfony\Component\Validator\Constraints as Assert;

#[CustomIdFormat]
final class InventoryItemCreateDTO
{
    #[Assert\NotBlank]
    public string $customId;

    public function __construct(string $customId = '')
    {
        $this->customId = $customId;
    }
}
