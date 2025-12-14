<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\InventoryItemCreateDTO;
use App\Entity\Inventory;
use App\Repository\InventoryItemRepository;
use App\Security\Voter\InventoryVoter;
use App\Service\InventoryItem\InventoryItemCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/inventories/{id}/items')]
final class InventoryItemController extends AbstractController
{
    #[Route('', name: 'inventory_items_index', methods: ['GET', 'POST'])]
    public function index(
        Inventory $inventory,
        Request $request,
        InventoryItemRepository $repository,
        InventoryItemCreator $creator
    ): Response {
        $this->denyAccessUnlessGranted(
            InventoryVoter::VIEW,
            $inventory
        );

        // Create item form
        $form = $this->createForm(
            \App\Form\InventoryItemType::class,
            new InventoryItemCreateDTO()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(
                InventoryVoter::CREATE_ITEM,
                $inventory
            );

            $creator->create(
                $inventory,
                $this->getUser(),
                $inventory->getCustomIdTemplate() // предполагается
            );

            return $this->redirectToRoute('inventory_items_index', [
                'id' => $inventory->getId(),
            ]);
        }

        return $this->render('inventory_item/index.html.twig', [
            'inventory' => $inventory,
            'items' => $repository->findByInventory($inventory),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/bulk-delete', name: 'inventory_items_bulk_delete', methods: ['POST'])]
    public function bulkDelete(
        Inventory $inventory,
        Request $request,
        InventoryItemRepository $repository
    ): Response {
        $this->denyAccessUnlessGranted(
            InventoryVoter::EDIT_ITEM,
            $inventory
        );

        $ids = $request->request->all('ids');

        if (!is_array($ids)) {
            return $this->redirectToRoute('inventory_items_index', [
                'id' => $inventory->getId(),
            ]);
        }

        $repository->deleteByIds($inventory, $ids);

        return $this->redirectToRoute('inventory_items_index', [
            'id' => $inventory->getId(),
        ]);
    }
}
