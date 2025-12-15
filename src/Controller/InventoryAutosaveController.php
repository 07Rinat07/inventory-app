<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\InventoryItem;
use App\Repository\InventoryItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class InventoryAutosaveController extends AbstractController
{
    #[Route(
        '/inventory-items/{id}/autosave',
        name: 'inventory_item_autosave',
        methods: ['PATCH']
    )]
    #[IsGranted('EDIT', subject: 'item')]
    public function autosave(
        InventoryItem $item,
        Request $request,
        EntityManagerInterface $em,
        InventoryItemRepository $repository
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(
                ['error' => 'Invalid JSON payload'],
                Response::HTTP_BAD_REQUEST
            );
        }

        /**
         * 1. Проверка optimistic locking
         */
        if (!isset($payload['version'])) {
            return $this->json(
                ['error' => 'Version is required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ((int) $payload['version'] !== $item->getVersion()) {
            return $this->json(
                [
                    'error' => 'Version conflict',
                    'currentVersion' => $item->getVersion(),
                ],
                Response::HTTP_CONFLICT
            );
        }

        /**
         * 2. Применяем изменения (минимально)
         * Здесь ТОЛЬКО разрешённые поля
         */
        if (isset($payload['customId'])) {
            $item->setCustomId((string) $payload['customId']);
        }

        /**
         * 3. Doctrine сам увеличит version
         */
        $em->flush();

        return $this->json([
            'status' => 'ok',
            'newVersion' => $item->getVersion(),
        ]);
    }
}
