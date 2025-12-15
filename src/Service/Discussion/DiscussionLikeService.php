<?php

declare(strict_types=1);

namespace App\Service\Discussion;

use App\Entity\DiscussionPost;
use App\Entity\DiscussionPostLike;
use App\Entity\User;
use App\Repository\DiscussionPostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DiscussionLikeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DiscussionPostLikeRepository $likeRepository,
    ) {
    }

    /**
     * Toggle like:
     * - если лайк есть → удалить
     * - если лайка нет → создать
     *
     * Возвращает текущее состояние (liked / unliked)
     */
    public function toggle(DiscussionPost $post, User $user): bool
    {
        return $this->em->wrapInTransaction(function () use ($post, $user): bool {
            if ($this->likeRepository->existsForUser($post, $user)) {
                $this->likeRepository->removeLike($post, $user);
                return false; // лайк убран
            }

            $like = new DiscussionPostLike($post, $user);
            $this->em->persist($like);

            return true; // лайк поставлен
        });
    }
}
