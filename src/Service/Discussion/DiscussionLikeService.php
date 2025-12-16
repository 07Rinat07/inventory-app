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
        private DiscussionPostLikeRepository $likeRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Toggle like:
     * - если лайк есть → удалить
     * - если нет → создать
     */
    public function toggle(User $user, DiscussionPost $post): array
    {
        $existingLike = $this->likeRepository->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if ($existingLike instanceof DiscussionPostLike) {
            $this->em->remove($existingLike);
            $this->em->flush();

            return [
                'liked' => false,
            ];
        }

        $like = new DiscussionPostLike($post, $user);
        $this->em->persist($like);
        $this->em->flush();

        return [
            'liked' => true,
        ];
    }
}
