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

    public function findOneByUserAndPost(
        User $user,
        DiscussionPost $post
    ): ?DiscussionPostLike {
        return $this->createQueryBuilder('l')
            ->andWhere('l.likedBy = :user')
            ->andWhere('l.post = :post')
            ->setParameter('user', $user)
            ->setParameter('post', $post)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByPost(DiscussionPost $post): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.post = :post')
            ->setParameter('post', $post)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
