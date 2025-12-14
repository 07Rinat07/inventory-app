<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InventoryItemFieldValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryItemFieldValueRepository::class)]
#[ORM\Table(
    name: 'inventory_item_field_values',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_item_field',
            columns: ['item_id', 'field_id']
        )
    ],
    indexes: [
        new ORM\Index(name: 'idx_value_item', columns: ['item_id']),
        new ORM\Index(name: 'idx_value_field', columns: ['field_id']),
    ]
)]
class InventoryItemFieldValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: InventoryItem::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private InventoryItem $item;

    #[ORM\ManyToOne(targetEntity: InventoryField::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private InventoryField $field;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value = null;

    public function __construct(
        InventoryItem $item,
        InventoryField $field,
        ?string $value
    ) {
        $this->item = $item;
        $this->field = $field;
        $this->value = $value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): InventoryItem
    {
        return $this->item;
    }

    public function getField(): InventoryField
    {
        return $this->field;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
