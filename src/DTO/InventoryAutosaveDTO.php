<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * DTO для автосохранения настроек Inventory.
 * Важно: version обязателен — это “ключ” optimistic locking.
 */
final class InventoryAutosaveDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $category,
        public readonly ?string $imageUrl,
        public readonly bool $isPublic,
        public readonly int $version,
    ) {
    }
}
