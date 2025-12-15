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

    public function existsForUser(DiscussionPost $post, User $user): bool
    {
        return (bool) $this->createQueryBuilder('l')
            ->select('1')
            ->andWhere('l.post = :post')
            ->andWhere('l.user = :user')
            ->setParameters([
                'post' => $post,
                'user' => $user,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeLike(DiscussionPost $post, User $user): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->andWhere('l.post = :post')
            ->andWhere('l.user = :user')
            ->setParameters([
                'post' => $post,
                'user' => $user,
            ])
            ->getQuery()
            ->execute();
    }
}
