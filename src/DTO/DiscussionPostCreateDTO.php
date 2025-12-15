<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class DiscussionPostCreateDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 5000)]
    public string $content;
}
