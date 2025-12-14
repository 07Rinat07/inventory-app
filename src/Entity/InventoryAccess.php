<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryAccessRepository::class)]
#[ORM\Table(
    name: 'inventory_access',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_inventory_user',
            columns: ['inventory_id', 'user_id']
        )
    ],
    indexes: [
        new ORM\Index(name: 'idx_access_inventory', columns: ['inventory_id']),
        new ORM\Index(name: 'idx_access_user', columns: ['user_id']),
    ]
)]
class InventoryAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    /**
     * Роль пользователя в рамках инвентаря:
     * OWNER | WRITE
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $role;

    public function __construct(
        Inventory $inventory,
        User $user,
        string $role
    ) {
        $this->inventory = $inventory;
        $this->user = $user;
        $this->role = $role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
