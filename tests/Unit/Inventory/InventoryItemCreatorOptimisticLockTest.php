<?php

declare(strict_types=1);

namespace App\Tests\Unit\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryItem\InventoryItemCreator;
use App\Service\CustomId\CustomIdGenerator;
use App\Repository\InventoryFieldRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use ReflectionClass;
use ReflectionProperty;

final class InventoryItemCreatorOptimisticLockTest extends KernelTestCase
{
    public function testOptimisticLockPreventsConcurrentItemModification(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /**
         * CustomIdGenerator — final + DI
         * Конструктор не вызываем, т.к. manualCustomId
         */
        $reflection = new ReflectionClass(CustomIdGenerator::class);
        $customIdGenerator = $reflection->newInstanceWithoutConstructor();

        $fieldRepository = $this->createMock(InventoryFieldRepository::class);
        $fieldRepository
            ->method('findBy')
            ->willReturn([]);

        $creator = new InventoryItemCreator(
            em: $em,
            customIdGenerator: $customIdGenerator,
            fieldRepository: $fieldRepository
        );

        // ============================
        // УНИКАЛЬНЫЙ User (важно!)
        // ============================

        $unique = uniqid('user_', true);

        $user = new User();
        $user->setEmail($unique . '@test.com');
        $user->setUsername($unique);
        $user->setPassword('hashed-password');

        // ============================
        // Создание Inventory
        // ============================

        $inventory = new Inventory(
            $user,
            'Test Inventory',
            'Test description',
            'default',
            true
        );

        $em->persist($user);
        $em->persist($inventory);
        $em->flush();

        // ============================
        // Создание InventoryItem
        // ============================

        $item = $creator->create(
            inventory: $inventory,
            actor: $user,
            fieldValues: [],
            manualCustomId: 'ITEM-LOCK-1'
        );

        // ============================
        // Обновляем item → version++
        // ============================

        $item->setCustomId('ITEM-LOCK-2');
        $em->flush();

        // ============================
        // Ломаем version (race condition)
        // ============================

        $versionProperty = new ReflectionProperty($item, 'version');
        $versionProperty->setAccessible(true);
        $versionProperty->setValue(
            $item,
            $versionProperty->getValue($item) - 1
        );

        $item->setCustomId('ITEM-LOCK-3');

        // ============================
        // EXPECT optimistic lock
        // ============================

        $this->expectException(OptimisticLockException::class);

        $em->flush();
    }
}
