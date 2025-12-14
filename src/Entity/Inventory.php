<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
#[ORM\Table(
    name: 'inventories',
    indexes: [
        new ORM\Index(name: 'idx_inventory_owner', columns: ['owner_id']),
        new ORM\Index(name: 'idx_inventory_public', columns: ['is_public']),
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'string', length: 100)]
    private string $category;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublic = false;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'inventory', targetEntity: InventoryItem::class)]
    private Collection $items;

    public function __construct(
        User $owner,
        string $title,
        string $description,
        string $category
    ) {
        $this->owner = $owner;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;

        $this->items = new ArrayCollection();

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

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }
}
