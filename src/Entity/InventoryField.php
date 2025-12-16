<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryFieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryFieldRepository::class)]
#[ORM\Table(name: 'inventory_fields')]
class InventoryField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(length: 50)]
    private string $type;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column]
    private int $position;

    public function __construct(
        Inventory $inventory,
        string $type,
        string $title,
        int $position
    ) {
        $this->inventory = $inventory;
        $this->type = $type;
        $this->title = $title;
        $this->position = $position;
    }

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

    public function getPosition(): int
    {
        return $this->position;
    }
}
