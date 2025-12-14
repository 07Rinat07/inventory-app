<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryItemRepository::class)]
#[ORM\Table(
    name: 'inventory_items',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_inventory_custom_id',
            columns: ['inventory_id', 'custom_id']
        )
    ],
    indexes: [
        new ORM\Index(name: 'idx_item_inventory', columns: ['inventory_id']),
        new ORM\Index(name: 'idx_item_created_by', columns: ['created_by']),
    ]
)]
#[ORM\HasLifecycleCallbacks]
class InventoryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Инвентарь, к которому принадлежит item
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Кастомный ID (человеко-читаемый)
     * Уникален в пределах одного инвентаря
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $customId;

    /**
     * Пользователь, создавший item
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $createdBy;

    /**
     * Версия для optimistic locking
     */
    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        Inventory $inventory,
        string $customId,
        User $createdBy
    ) {
        $this->inventory = $inventory;
        $this->customId = $customId;
        $this->createdBy = $createdBy;

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ===== Getters =====

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getCustomId(): string
    {
        return $this->customId;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
