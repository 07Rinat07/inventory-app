<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryEditForeignUserVoterTest extends TestCase
{
    public function testUserCannotEditForeignInventory(): void
    {
        // ─────────────────────────────
        // Mock репозитория ACL (доступов нет)
        // ─────────────────────────────
        $accessRepository = $this->createMock(InventoryAccessRepository::class);
        $accessRepository
            ->method('findOneBy')
            ->willReturn(null);

        $voter = new InventoryVoter($accessRepository);

        // ─────────────────────────────
        // Владелец inventory
        // ─────────────────────────────
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // ─────────────────────────────
        // Чужой пользователь
        // ─────────────────────────────
        $foreignUser = new User();
        $foreignUser->setEmail('user@test.com');

        // ─────────────────────────────
        // Inventory принадлежит owner
        // ─────────────────────────────
        $inventory = new Inventory(
            $owner,
            'Foreign inventory',
            'Description',
            'Category'
        );

        // ─────────────────────────────
        // Security token чужого пользователя
        // ─────────────────────────────
        $token = new UsernamePasswordToken(
            $foreignUser,
            'main',
            $foreignUser->getRoles()
        );

        // ─────────────────────────────
        // Голосуем
        // ─────────────────────────────
        $result = $voter->vote(
            $token,
            $inventory,
            [InventoryVoter::EDIT]
        );

        // ─────────────────────────────
        // Проверка
        // ─────────────────────────────
        $this->assertSame(
            InventoryVoter::ACCESS_DENIED,
            $result
        );
    }
}
