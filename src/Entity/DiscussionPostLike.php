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
            name: 'uniq_post_user_like',
            columns: ['post_id', 'user_id']
        )
    ]
)]
class DiscussionPostLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DiscussionPost::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DiscussionPost $post;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function __construct(DiscussionPost $post, User $user)
    {
        $this->post = $post;
        $this->user = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): DiscussionPost
    {
        return $this->post;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
