<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\InventoryField;
use App\Entity\InventoryItem;
use App\Entity\InventoryItemFieldValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InventoryItemFieldValue>
 */
final class InventoryItemFieldValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryItemFieldValue::class);
    }

    /**
     * Возвращает значение кастомного поля для item
     * Используется автосейвом
     */
    public function findOneByItemAndField(
        InventoryItem $item,
        InventoryField $field
    ): ?InventoryItemFieldValue {
        return $this->findOneBy([
            'item' => $item,
            'field' => $field,
        ]);
    }
}
