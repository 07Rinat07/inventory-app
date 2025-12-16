<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DiscussionPost;
use App\Entity\DiscussionPostLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DiscussionPostLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscussionPostLike::class);
    }

    /**
     * Возвращает:
     * - общее количество лайков
     * - лайкнул ли конкретный пользователь
     *
     * ❗ Один запрос, без SELECT *
     */
    public function getLikeStats(DiscussionPost $post, ?User $user): array
    {
        $qb = $this->createQueryBuilder('l')
            ->select(
                'COUNT(l.id) AS likeCount',
                'SUM(CASE WHEN l.user = :user THEN 1 ELSE 0 END) AS userLiked'
            )
            ->where('l.post = :post')
            ->setParameter('post', $post)
            ->setParameter('user', $user);

        $result = $qb->getQuery()->getSingleResult();

        return [
            'count' => (int) $result['likeCount'],
            'liked' => $user ? ((int) $result['userLiked'] > 0) : false,
        ];
    }
}
