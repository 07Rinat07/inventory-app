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
        ),
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
     * Owning side связи.
     * ОБЯЗАТЕЛЬНО inversedBy="items"
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $createdBy;

    /**
     * Редактируемый custom ID
     * Уникален внутри inventory (через составной индекс)
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $customId;

    /**
     * Optimistic locking
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
        User $createdBy,
        string $customId
    ) {
        $this->inventory = $inventory;
        $this->createdBy = $createdBy;
        $this->customId = $customId;

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

    public function setCustomId(string $customId): void
    {
        $this->customId = $customId;
    }
}
