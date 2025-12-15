<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для автосейва одного значения кастомного поля item
 * Используется ТОЛЬКО для PATCH-запроса автосейва
 */
final class InventoryItemFieldValueAutosaveDTO
{
    /**
     * ID поля (InventoryField.id)
     */
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    public int $fieldId;

    /**
     * Новое значение поля
     * Храним как string, тип интерпретируется по field.type
     */
    #[Assert\Type('string')]
    public ?string $value = null;

    /**
     * Версия InventoryItem для optimistic locking
     */
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    public int $version;
}
