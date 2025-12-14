<?php

declare(strict_types=1);

namespace App\Domain\CustomId;

final class CustomIdTemplate
{
    /**
     * @param CustomIdPart[] $parts
     */
    public function __construct(
        private array $parts
    ) {
    }

    /**
     * @return CustomIdPart[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}
