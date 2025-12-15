<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\CustomId\CustomIdPart;
use App\Domain\CustomId\CustomIdTemplate;
use App\Entity\Inventory;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * Генератор Custom ID для InventoryItem.
 *
 * ВАЖНО:
 * - Генерация НЕ гарантирует уникальность → это делает БД
 * - При конфликте должен быть retry на уровне InventoryItemCreator
 * - Поддерживает все типы частей из ТЗ
 */
final class CustomIdGenerator
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Генерирует Custom ID для конкретного inventory
     * на основе шаблона, привязанного к inventory.
     */
    public function generateForInventory(
        Inventory $inventory
    ): string {
        $template = $inventory->getCustomIdTemplate();

        if (!$template instanceof CustomIdTemplate) {
            throw new \LogicException('Inventory has no Custom ID template configured.');
        }

        $parts = [];

        foreach ($template->getParts() as $part) {
            $parts[] = $this->generatePart($inventory, $part);
        }

        return implode('', $parts);
    }

    /**
     * Генерация одной части ID
     */
    private function generatePart(
        Inventory $inventory,
        CustomIdPart $part
    ): string {
        return match ($part->getType()) {
            CustomIdPart::TYPE_TEXT =>
            (string) ($part->getOptions()['value'] ?? ''),

            CustomIdPart::TYPE_RANDOM_20BIT =>
            (string) random_int(0, (1 << 20) - 1),

            CustomIdPart::TYPE_RANDOM_32BIT =>
            (string) random_int(0, (1 << 32) - 1),

            CustomIdPart::TYPE_RANDOM_6DIGIT =>
            str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),

            CustomIdPart::TYPE_RANDOM_9DIGIT =>
            str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),

            CustomIdPart::TYPE_GUID =>
            Uuid::uuid4()->toString(),

            CustomIdPart::TYPE_DATE =>
            (new \DateTimeImmutable())->format('Ymd'),

            CustomIdPart::TYPE_DATETIME =>
            (new \DateTimeImmutable())->format('YmdHis'),

            CustomIdPart::TYPE_SEQUENCE =>
            $this->generateSequence($inventory, $part),

            default =>
            throw new \LogicException('Unknown Custom ID part type: ' . $part->getType()),
        };
    }

    /**
     * Генерация SEQUENCE части.
     *
     * ВАЖНО:
     * - мы НЕ парсим custom_id
     * - мы НЕ полагаемся на MAX(custom_id)
     * - уникальность всё равно обеспечивает UNIQUE(inventory_id, custom_id)
     */
    private function generateSequence(
        Inventory $inventory,
        CustomIdPart $part
    ): string {
        $options = $part->getOptions();

        $start = (int) ($options['start'] ?? 1);
        $length = (int) ($options['length'] ?? 0);

        $sql = '
            SELECT COUNT(*) + :start
            FROM inventory_items
            WHERE inventory_id = :inventoryId
        ';

        $next = (int) $this->connection->fetchOne($sql, [
            'inventoryId' => $inventory->getId(),
            'start' => $start,
        ]);

        $value = (string) $next;

        if ($length > 0) {
            $value = str_pad($value, $length, '0', STR_PAD_LEFT);
        }

        return $value;
    }
}
