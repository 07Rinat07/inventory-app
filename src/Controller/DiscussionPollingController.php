<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inventory;
use App\Repository\DiscussionPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscussionPollingController extends AbstractController
{
    #[Route(
        '/inventories/{id}/discussion/poll',
        name: 'discussion_poll',
        methods: ['GET']
    )]
    public function poll(
        Inventory $inventory,
        Request $request,
        DiscussionPostRepository $repository
    ): Response {
        /**
         * Параметр after:
         * ISO-строка даты последнего полученного сообщения
         * пример: 2025-12-15T10:30:00Z
         */
        $after = $request->query->get('after');

        $afterDate = $after
            ? new \DateTimeImmutable($after)
            : new \DateTimeImmutable('-5 seconds');

        $posts = $repository->findNewPosts($inventory, $afterDate);

        return $this->json(array_map(
            static function ($post): array {
                return [
                    'id' => $post->getId(),
                    'author' => $post->getAuthor()->getUsername(),
                    'message' => $post->getMessage(),
                    'createdAt' => $post->getCreatedAt()->format(DATE_ATOM),
                ];
            },
            $posts
        ));
    }
}
