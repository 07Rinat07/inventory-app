<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\InventoryItemFieldValueAutosaveDTO;
use App\Entity\InventoryItem;
use App\Entity\InventoryItemFieldValue;
use App\Repository\InventoryFieldRepository;
use App\Repository\InventoryItemFieldValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class InventoryItemFieldValueAutosaveController extends AbstractController
{
    #[Route(
        '/inventory-items/{id}/fields/autosave',
        name: 'inventory_item_field_autosave',
        methods: ['PATCH']
    )]
    #[IsGranted('EDIT', subject: 'item')]
    public function autosave(
        InventoryItem $item,
        Request $request,
        InventoryFieldRepository $fieldRepository,
        InventoryItemFieldValueRepository $valueRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        $dto = new InventoryItemFieldValueAutosaveDTO();
        $data = json_decode($request->getContent(), true);

        $dto->fieldId = $data['fieldId'] ?? null;
        $dto->value = $data['value'] ?? null;
        $dto->version = $data['version'] ?? null;

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => 'Validation failed'], 400);
        }

        // optimistic locking
        if ($item->getVersion() !== $dto->version) {
            return $this->json(['error' => 'Version conflict'], 409);
        }

        $field = $fieldRepository->find($dto->fieldId);
        if (!$field || $field->getInventory() !== $item->getInventory()) {
            return $this->json(['error' => 'Invalid field'], 400);
        }

        $value = $valueRepository->findOneByItemAndField($item, $field);

        if (!$value) {
            $value = new InventoryItemFieldValue($item, $field);
            $em->persist($value);
        }

        $value->setValue($dto->value);

        $em->flush();

        return $this->json([
            'status' => 'ok',
            'newVersion' => $item->getVersion(),
        ]);
    }
}
