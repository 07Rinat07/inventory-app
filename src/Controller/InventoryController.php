<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventories')]
final class InventoryController extends AbstractController
{
    #[Route('', name: 'inventories_index', methods: ['GET'])]
    public function index(InventoryRepository $repository): Response
    {
        /**
         * ВАЖНО:
         * Не используем findAll(), чтобы не было полного сканирования таблицы.
         * Даже если сейчас данных мало — архитектура должна быть корректной.
         */
        $inventories = $repository->findPublicOrOwnedByUser(
            $this->getUser()
        );

        return $this->render('inventory/index.html.twig', [
            'inventories' => $inventories,
        ]);
    }

    #[Route('/{id}', name: 'inventories_show', methods: ['GET'])]
    public function show(
        Inventory $inventory
    ): Response {
        /**
         * ParamConverter:
         * Symfony сам загрузит Inventory по id или вернёт 404
         */
        $this->denyAccessUnlessGranted(
            'INVENTORY_VIEW',
            $inventory
        );

        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
        ]);
    }
}
