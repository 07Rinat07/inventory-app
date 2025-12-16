<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DiscussionPost;
use App\Security\Voter\InventoryVoter;
use App\Service\DiscussionLikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DiscussionLikeStatsController extends AbstractController
{
    #[Route(
        '/discussion-posts/{id}/likes',
        name: 'discussion_post_likes_stats',
        methods: ['GET']
    )]
    public function stats(
        DiscussionPost $post,
        DiscussionLikeService $likeService
    ): JsonResponse {
        // ACL: можно читать, если есть доступ к инвентарю поста
        $this->denyAccessUnlessGranted(
            InventoryVoter::VIEW,
            $post->getInventory()
        );

        $user = $this->getUser();

        $stats = $likeService->getStats(
            $post,
            $user instanceof \App\Entity\User ? $user : null
        );

        return $this->json([
            'likes' => $stats['count'],
            'liked' => $stats['liked'],
        ]);
    }
}
