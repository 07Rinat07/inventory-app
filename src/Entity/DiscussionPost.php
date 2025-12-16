<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DiscussionPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscussionPostRepository::class)]
#[ORM\Table(name: 'discussion_post')]
class DiscussionPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'discussionPosts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Inventory $inventory,
        User $author,
        string $message
    ) {
        $this->inventory = $inventory;
        $this->author = $author;
        $this->message = $message;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
