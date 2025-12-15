<?php

declare(strict_types=1);

namespace App\Service\InventoryItem;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Entity\User;
use App\Service\CustomId\CustomIdGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Единственная точка создания InventoryItem.
 *
 * Почему отдельный сервис:
 * - контроллер должен быть тонким
 * - Entity не должна знать про транзакции/ретраи/уникальные индексы
 * - здесь удобно централизовать бизнес-правила создания item
 *
 * Что гарантируем:
 * - создание происходит в транзакции
 * - custom_id уникален на уровне БД (UNIQUE(inventory_id, custom_id))
 * - при конфликте custom_id мы делаем retry ограниченное число раз
 * - если пользователь ввёл custom_id вручную и он конфликтует — НЕ "чинить" автоматически,
 *   а вернуть понятную доменную ошибку (как требует ТЗ)
 */
final class InventoryItemCreator
{
    /**
     * Сколько раз пытаемся сгенерировать новый custom_id при конфликте.
     * Для random/sequence/GUID конфликт маловероятен, но при параллельности возможен.
     */
    private const MAX_GENERATION_RETRIES = 5;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CustomIdGenerator $customIdGenerator,
    ) {
    }

    /**
     * Создаёт новый item.
     *
     * @param Inventory $inventory      Инвентарь, куда добавляем item
     * @param User      $actor          Текущий пользователь (создатель item)
     * @param string|null $manualCustomId Если пользователь вручную ввёл custom_id (редактируемость)
     *
     * @throws \DomainException если manualCustomId конфликтует или если не удалось сгенерировать уникальный ID
     */
    public function create(
        Inventory $inventory,
        User $actor,
        ?string $manualCustomId = null
    ): InventoryItem {
        // 1) Если custom_id ввёл пользователь — пробуем создать ровно с ним.
        //    ВАЖНО: при конфликте НЕ делаем автоподмену, по ТЗ пользователь должен править вручную.
        if ($manualCustomId !== null && trim($manualCustomId) !== '') {
            return $this->createWithCustomIdOnce($inventory, $actor, trim($manualCustomId));
        }

        // 2) Если custom_id не задан — генерируем системой, но с ограниченным retry.
        $lastError = null;

        for ($attempt = 1; $attempt <= self::MAX_GENERATION_RETRIES; $attempt++) {
            $generated = $this->customIdGenerator->generateForInventory($inventory);

            try {
                return $this->createWithCustomIdOnce($inventory, $actor, $generated);
            } catch (\DomainException $e) {
                // Здесь DomainException означает конфликт custom_id на уровне БД.
                // Для автогенерации это нормально: пробуем ещё раз с новым ID.
                $lastError = $e;
            }
        }

        // Если даже после retry не получилось — это уже сигнал, что что-то не так с форматом/генератором/параллельностью.
        throw new \DomainException(
            'Could not generate a unique Custom ID. Please try again or enter Custom ID manually.',
            previous: $lastError
        );
    }

    /**
     * Создание item в транзакции с одним конкретным custom_id.
     *
     * Важно:
     * - уникальность обеспечивает БД (UNIQUE(inventory_id, custom_id))
     * - при конфликте ловим UniqueConstraintViolationException
     * - обязательно rollback перед повтором/выходом
     */
    private function createWithCustomIdOnce(
        Inventory $inventory,
        User $actor,
        string $customId
    ): InventoryItem {
        $this->em->beginTransaction();

        try {
            $item = new InventoryItem(
                inventory: $inventory,
                createdBy: $actor,
                customId: $customId
            );

            $this->em->persist($item);

            // flush нужен внутри транзакции, чтобы:
            // - БД проверила уникальные ограничения
            // - мы поймали исключение здесь же
            $this->em->flush();

            $this->em->commit();

            return $item;
        } catch (UniqueConstraintViolationException $e) {
            $this->safeRollback();

            /**
             * ВАЖНО: Не "лечим" данные автоматически.
             * - Если custom_id ручной — пользователь должен исправить сам (требование ТЗ)
             * - Если custom_id генерировался системой — верхний уровень сделает retry
             */
            throw new \DomainException('Custom ID already exists in this inventory.', previous: $e);
        } catch (\Throwable $e) {
            $this->safeRollback();
            throw $e; // пробрасываем дальше — это реальная ошибка
        }
    }

    /**
     * Rollback иногда может бросить исключение, если транзакция уже закрыта/в ошибочном состоянии.
     * Мы не хотим скрыть исходную ошибку — поэтому rollback делаем "безопасно".
     */
    private function safeRollback(): void
    {
        try {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
        } catch (\Throwable) {
            // намеренно игнорируем, чтобы не затереть первопричину
        }
    }
}
