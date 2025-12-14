<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\CustomId\CustomIdPart;
use App\Domain\CustomId\CustomIdTemplate;
use App\Entity\Inventory;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class CustomIdGenerator
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Генерирует custom_id для item
     */
    public function generate(Inventory $inventory, CustomIdTemplate $template): string
    {
        $parts = [];

        foreach ($template->getParts() as $part) {
            $parts[] = $this->generatePart($inventory, $part);
        }

        return implode('', $parts);
    }

    private function generatePart(
        Inventory $inventory,
        CustomIdPart $part
    ): string {
        return match ($part->getType()) {
            'TEXT' => (string) ($part->getOptions()['value'] ?? ''),
            'RANDOM_20BIT' => (string) random_int(0, (1 << 20) - 1),
            'RANDOM_32BIT' => (string) random_int(0, (1 << 32) - 1),
            'RANDOM_6DIGIT' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'RANDOM_9DIGIT' => str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
            'GUID' => Uuid::uuid4()->toString(),
            'DATE' => (new \DateTimeImmutable())->format('Ymd'),
            'DATETIME' => (new \DateTimeImmutable())->format('YmdHis'),
            'SEQUENCE' => $this->nextSequence($inventory),
            default => throw new \LogicException('Unknown Custom ID part type'),
        };
    }

    /**
     * Последовательный номер (max + 1) внутри одного inventory
     * Используется транзакция для защиты от гонок
     */
    private function nextSequence(Inventory $inventory): string
    {
        return (string) $this->connection->transactional(function () use ($inventory) {
            $sql = '
                SELECT COALESCE(MAX(custom_id::int), 0) + 1
                FROM inventory_items
                WHERE inventory_id = :inventoryId
                FOR UPDATE
            ';

            $value = $this->connection->fetchOne($sql, [
                'inventoryId' => $inventory->getId(),
            ]);

            return (string) $value;
        });
    }
}
