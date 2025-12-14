<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InventoryItem>
 *
 * Репозиторий для работы с InventoryItem.
 * Важные правила (по требованиям менторов):
 * - не делать запросы к БД в циклах
 * - использовать bulk-операции там, где это возможно
 * - не полагаться на "SELECT *" по умолчанию (используем QueryBuilder)
 */
final class InventoryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryItem::class);
    }

    /**
     * Возвращает items конкретного инвентаря (табличный вывод).
     *
     * @return InventoryItem[]
     */
    public function findByInventory(Inventory $inventory, int $limit = 200): array
    {
        // limit нужен для защиты от слишком больших таблиц на раннем этапе
        // позже можно заменить на пагинацию (Paginator / Pagerfanta)
        return $this->createQueryBuilder('i')
            ->andWhere('i.inventory = :inventory')
            ->setParameter('inventory', $inventory)
            ->orderBy('i.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Bulk delete: удаляет items по списку id в рамках одного инвентаря.
     * Это важно для требования "bulk actions" и запрета запросов в циклах.
     *
     * @param array<int, int|string> $ids
     */
    public function deleteByIds(Inventory $inventory, array $ids): int
    {
        // Чистим вход, чтобы не отправлять мусор в запрос
        $ids = array_values(array_filter(array_map(
            static fn ($v) => is_numeric($v) ? (int) $v : null,
            $ids
        ), static fn ($v) => is_int($v) && $v > 0));

        if ($ids === []) {
            return 0;
        }

        // Один DELETE запрос, без циклов.
        return $this->createQueryBuilder('i')
            ->delete()
            ->andWhere('i.inventory = :inventory')
            ->andWhere('i.id IN (:ids)')
            ->setParameter('inventory', $inventory)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    /**
     * Получить item по id, но строго в рамках конкретного инвентаря.
     * Полезно для view/edit, чтобы избежать доступа к чужим items через прямой id.
     */
    public function findOneByInventoryAndId(Inventory $inventory, int $id): ?InventoryItem
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.inventory = :inventory')
            ->andWhere('i.id = :id')
            ->setParameter('inventory', $inventory)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Получить набор items по ids в рамках инвентаря.
     * Пригодится для export / массовых операций.
     *
     * @param int[] $ids
     * @return InventoryItem[]
     */
    public function findByInventoryAndIds(Inventory $inventory, array $ids): array
    {
        $ids = array_values(array_filter($ids, static fn ($v) => is_int($v) && $v > 0));

        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('i')
            ->andWhere('i.inventory = :inventory')
            ->andWhere('i.id IN (:ids)')
            ->setParameter('inventory', $inventory)
            ->setParameter('ids', $ids)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
