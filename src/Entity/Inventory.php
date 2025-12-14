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
    /**
     * Технический первичный ключ.
     * Не используется в UI.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Владелец инвентаря.
     * Используется для ACL и проверок доступа.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    /**
     * Название инвентаря.
     * Отображается в списках и на главной.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /**
     * Описание инвентаря (Markdown).
     * Не используется в поиске напрямую.
     */
    #[ORM\Column(type: 'text')]
    private string $description;

    /**
     * Категория инвентаря.
     * Список категорий управляется напрямую через БД.
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $category;

    /**
     * URL изображения (обложки).
     * ❗ Файл НЕ хранится в БД и НЕ загружается на сервер.
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $imageUrl = null;

    /**
     * Флаг публичности.
     * Если true — все авторизованные пользователи имеют write-доступ к items.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isPublic = false;

    /**
     * Версия для optimistic locking.
     * Используется при редактировании инвентаря.
     */
    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    /**
     * Дата создания.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * Дата последнего обновления.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /**
     * Items инвентаря.
     * Lazy loading, без каскадного удаления значений.
     */
    #[ORM\OneToMany(mappedBy: 'inventory', targetEntity: InventoryItem::class)]
    private Collection $items;

    /**
     * Кастомные поля инвентаря.
     */
    #[ORM\OneToMany(mappedBy: 'inventory', targetEntity: InventoryField::class)]
    private Collection $fields;

    public function __construct(User $owner, string $title, string $description, string $category)
    {
        $this->owner = $owner;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;

        $this->items = new ArrayCollection();
        $this->fields = new ArrayCollection();

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ========================
    // Getters (минимально нужные)
    // ========================

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

    public function getFields(): Collection
    {
        return $this->fields;
    }
}
