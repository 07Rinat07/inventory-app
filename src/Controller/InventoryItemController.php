<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\InventoryItemCreateDTO;
use App\DTO\InventoryItemEditDTO;
use App\Entity\InventoryItem;
use App\Repository\InventoryItemRepository;
use App\Repository\InventoryRepository;
use App\Security\Voter\InventoryVoter;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\InventoryItemType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventories/{id}/items')]
final class InventoryItemController extends AbstractController
{
    #[Route('', name: 'inventory_items_index', methods: ['GET', 'POST'])]
    public function index(
        int $id,
        Request $request,
        InventoryRepository $inventories,
        InventoryItemRepository $items,
        EntityManagerInterface $em
    ): Response {
        $inventory = $inventories->find($id);
        if (!$inventory) {
            throw $this->createNotFoundException();
        }

        // ACL: VIEW
        $this->denyAccessUnlessGranted(
            InventoryVoter::VIEW,
            $inventory
        );

        $dto = new InventoryItemCreateDTO();
        $form = $this->createForm(InventoryItemType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ACL: CREATE
            $this->denyAccessUnlessGranted(
                InventoryVoter::CREATE_ITEM,
                $inventory
            );

            $item = new InventoryItem(
                $inventory,
                $this->getUser(),
                $dto->customId
            );

            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute(
                'inventory_items_index',
                ['id' => $id]
            );
        }

        return $this->render('inventory_item/index.html.twig', [
            'inventory' => $inventory,
            'items'     => $items->findByInventory($inventory),
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{itemId}/edit', name: 'inventory_item_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        int $itemId,
        Request $request,
        InventoryRepository $inventories,
        InventoryItemRepository $items,
        EntityManagerInterface $em
    ): Response {
        $inventory = $inventories->find($id);
        $item = $items->find($itemId);

        if (!$inventory || !$item || $item->getInventory()->getId() !== $inventory->getId()) {
            throw $this->createNotFoundException();
        }

        // ACL: EDIT
        $this->denyAccessUnlessGranted(
            InventoryVoter::EDIT_ITEM,
            $inventory
        );

        $dto = new InventoryItemEditDTO();
        $dto->customId = $item->getCustomId();

        $form = $this->createForm(InventoryItemType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item->setCustomId($dto->customId);
            $em->flush();

            return $this->redirectToRoute(
                'inventory_items_index',
                ['id' => $id]
            );
        }

        return $this->render('inventory_item/edit.html.twig', [
            'inventory' => $inventory,
            'item'      => $item,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{itemId}/delete', name: 'inventory_item_delete', methods: ['POST'])]
    public function delete(
        int $id,
        int $itemId,
        Request $request,
        InventoryRepository $inventories,
        InventoryItemRepository $items,
        EntityManagerInterface $em
    ): Response {
        $inventory = $inventories->find($id);
        $item = $items->find($itemId);

        if (!$inventory || !$item || $item->getInventory()->getId() !== $inventory->getId()) {
            throw $this->createNotFoundException();
        }

        // ACL: DELETE = EDIT_ITEM
        $this->denyAccessUnlessGranted(
            InventoryVoter::EDIT_ITEM,
            $inventory
        );

        // CSRF protection
        if (!$this->isCsrfTokenValid(
            'delete_item_' . $item->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($item);
        $em->flush();

        return $this->redirectToRoute(
            'inventory_items_index',
            ['id' => $id]
        );
    }
}
