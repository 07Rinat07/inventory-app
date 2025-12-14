<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryFieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryFieldRepository::class)]
#[ORM\Table(
    name: 'inventory_fields',
    indexes: [
        new ORM\Index(name: 'idx_field_inventory', columns: ['inventory_id']),
        new ORM\Index(name: 'idx_field_type', columns: ['type']),
    ]
)]
class InventoryField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Инвентарь, к которому относится поле
     */
    #[ORM\ManyToOne(targetEntity: Inventory::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    /**
     * Тип поля (ограниченный набор)
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $type;

    /**
     * Заголовок поля (можно менять)
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /**
     * Описание поля (tooltip / hint)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Показывать ли поле в таблице items
     */
    #[ORM\Column(type: 'boolean')]
    private bool $showInTable = false;

    /**
     * Позиция поля (для drag-and-drop)
     */
    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    public function __construct(
        Inventory $inventory,
        string $type,
        string $title
    ) {
        $this->inventory = $inventory;
        $this->type = $type;
        $this->title = $title;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isShowInTable(): bool
    {
        return $this->showInTable;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
