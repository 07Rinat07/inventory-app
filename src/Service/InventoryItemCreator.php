<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class InventoryItemCreator
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function create(
        Inventory $inventory,
        User $user,
        string $customId
    ): InventoryItem {
        $item = new InventoryItem(
            inventory: $inventory,
            createdBy: $user,
            customId: $customId
        );

        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }
}
