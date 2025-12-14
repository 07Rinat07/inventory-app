<?php

declare(strict_types=1);

namespace App\Domain\CustomId;

final class CustomIdPart
{
    public function __construct(
        private string $type,
        private array $options = []
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
