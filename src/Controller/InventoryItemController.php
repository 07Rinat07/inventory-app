<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\InventoryItemCreateDTO;
use App\Entity\Inventory;
use App\Repository\InventoryItemRepository;
use App\Repository\InventoryRepository;
use App\Security\Voter\InventoryVoter;
use App\Service\InventoryItemCreator;
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
        InventoryItemRepository $repository,
        InventoryItemCreator $creator
    ): Response {
        /** @var Inventory $inventory */
        $inventory = $inventories->find($id);

        if (!$inventory) {
            throw $this->createNotFoundException();
        }

        // ACL: просмотр
        $this->denyAccessUnlessGranted(
            InventoryVoter::VIEW,
            $inventory
        );

        // DTO
        $dto = new InventoryItemCreateDTO();

        // Form
        $form = $this->createForm(
            InventoryItemType::class,
            $dto
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ACL: создание
            $this->denyAccessUnlessGranted(
                InventoryVoter::CREATE_ITEM,
                $inventory
            );

            $creator->create(
                $inventory,
                $this->getUser(),
                $dto->customId
            );

            return $this->redirectToRoute(
                'inventory_items_index',
                ['id' => $inventory->getId()]
            );
        }

        return $this->render('inventory_item/index.html.twig', [
            'inventory' => $inventory,
            'items'     => $repository->findByInventory($inventory),
            'form'      => $form->createView(),
        ]);
    }
}
