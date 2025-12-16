<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DiscussionPost;
use App\Service\Discussion\DiscussionLikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DiscussionLikeController extends AbstractController
{
    #[Route(
        '/discussion-posts/{id}/like',
        name: 'discussion_post_like',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_USER')]
    public function like(
        DiscussionPost $post,
        DiscussionLikeService $likeService
    ): JsonResponse {
        $result = $likeService->toggle(
            $this->getUser(),
            $post
        );

        return $this->json($result);
    }
}
