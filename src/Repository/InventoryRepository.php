<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
final class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    /**
     * Возвращает список инвентарей:
     *  - публичные
     *  - или принадлежащие текущему пользователю
     *
     * ВАЖНО:
     *  - без findAll()
     *  - без полного сканирования таблицы
     *  - с ограничением результата
     */
    public function findPublicOrOwnedByUser(?User $user, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('i')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults($limit);

        if ($user instanceof User) {
            $qb
                ->where('i.isPublic = true')
                ->orWhere('i.owner = :user')
                ->setParameter('user', $user);
        } else {
            $qb->where('i.isPublic = true');
        }

        return $qb->getQuery()->getResult();
    }
}
