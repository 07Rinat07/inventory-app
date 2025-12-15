<?php

declare(strict_types=1);

namespace App\Service\InventoryItem;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Entity\InventoryItemFieldValue;
use App\Entity\User;
use App\Repository\InventoryFieldRepository;
use App\Service\CustomId\CustomIdGenerator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Единственная точка создания InventoryItem и его значений.
 */
final class InventoryItemCreator
{
    private const MAX_GENERATION_RETRIES = 5;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CustomIdGenerator $customIdGenerator,
        private readonly InventoryFieldRepository $fieldRepository,
    ) {
    }

    /**
     * @param array<int, mixed> $fieldValues fieldId => value
     */
    public function create(
        Inventory $inventory,
        User $actor,
        array $fieldValues,
        ?string $manualCustomId = null
    ): InventoryItem {
        if ($manualCustomId !== null && trim($manualCustomId) !== '') {
            return $this->createOnce(
                $inventory,
                $actor,
                trim($manualCustomId),
                $fieldValues
            );
        }

        $lastError = null;

        for ($i = 1; $i <= self::MAX_GENERATION_RETRIES; $i++) {
            $customId = $this->customIdGenerator->generateForInventory($inventory);

            try {
                return $this->createOnce(
                    $inventory,
                    $actor,
                    $customId,
                    $fieldValues
                );
            } catch (\DomainException $e) {
                $lastError = $e;
            }
        }

        throw new \DomainException(
            'Unable to generate unique Custom ID.',
            previous: $lastError
        );
    }

    /**
     * @param array<int, mixed> $fieldValues
     */
    private function createOnce(
        Inventory $inventory,
        User $actor,
        string $customId,
        array $fieldValues
    ): InventoryItem {
        $this->em->beginTransaction();

        try {
            $item = new InventoryItem(
                inventory: $inventory,
                createdBy: $actor,
                customId: $customId
            );

            $this->em->persist($item);

            // Загружаем все поля инвентаря ОДНИМ запросом
            $fields = $this->fieldRepository->findBy([
                'inventory' => $inventory,
            ]);

            $fieldsById = [];
            foreach ($fields as $field) {
                $fieldsById[$field->getId()] = $field;
            }

            foreach ($fieldValues as $fieldId => $value) {
                if (!isset($fieldsById[$fieldId])) {
                    continue; // поле удалено или не относится к inventory
                }

                $field = $fieldsById[$fieldId];

                if (!$this->isValueCompatible($field->getType(), $value)) {
                    throw new \DomainException(
                        sprintf('Invalid value for field "%s".', $field->getTitle())
                    );
                }

                $fieldValue = new InventoryItemFieldValue(
                    item: $item,
                    field: $field,
                    value: $value === null ? null : (string) $value
                );

                $this->em->persist($fieldValue);
            }

            $this->em->flush();
            $this->em->commit();

            return $item;
        } catch (UniqueConstraintViolationException $e) {
            $this->safeRollback();
            throw new \DomainException('Custom ID already exists.', previous: $e);
        } catch (\Throwable $e) {
            $this->safeRollback();
            throw $e;
        }
    }

    private function isValueCompatible(string $type, mixed $value): bool
    {
        return match ($type) {
            'single_text', 'multi_text' =>
                is_string($value) || $value === null,

            'number' =>
                is_numeric($value) || $value === null,

            'boolean' =>
                is_bool($value) || $value === null,

            'document', 'image' =>
                is_string($value) || $value === null,

            default => false,
        };
    }

    private function safeRollback(): void
    {
        try {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
        } catch (\Throwable) {
        }
    }
}
