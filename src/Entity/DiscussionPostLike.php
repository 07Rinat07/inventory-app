<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DiscussionPostLikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscussionPostLikeRepository::class)]
#[ORM\Table(
    name: 'discussion_post_likes',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_like_user_post',
            columns: ['liked_by_id', 'post_id']
        )
    ],
    indexes: [
        new ORM\Index(name: 'idx_like_post', columns: ['post_id']),
        new ORM\Index(name: 'idx_like_user', columns: ['liked_by_id']),
    ]
)]
class DiscussionPostLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Пользователь, поставивший лайк
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $likedBy;

    /**
     * Сообщение, которое лайкнули
     */
    #[ORM\ManyToOne(targetEntity: DiscussionPost::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DiscussionPost $post;

    /**
     * Дата и время лайка
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $likedBy, DiscussionPost $post)
    {
        $this->likedBy = $likedBy;
        $this->post = $post;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLikedBy(): User
    {
        return $this->likedBy;
    }

    public function getPost(): DiscussionPost
    {
        return $this->post;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
