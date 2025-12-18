<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryEditWriteAccessVoterTest extends TestCase
{
    public function testUserWithWriteAccessCanEditInventory(): void
    {
        // ─────────────────────────────
        // Пользователь
        // ─────────────────────────────
        $user = new User();
        $user->setEmail('user@test.com');

        // ─────────────────────────────
        // Владелец inventory
        // ─────────────────────────────
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // ─────────────────────────────
        // Inventory
        // ─────────────────────────────
        $inventory = new Inventory(
            $owner,
            'Shared inventory',
            'Description',
            'Category'
        );

        // ─────────────────────────────
        // ACL с ролью WRITE
        // ─────────────────────────────
        $access = new InventoryAccess(
            $inventory,
            $user,
            'WRITE'
        );

        // ─────────────────────────────
        // Mock репозитория ACL
        // ─────────────────────────────
        $accessRepository = $this->createMock(InventoryAccessRepository::class);
        $accessRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'inventory' => $inventory,
                'user'      => $user,
            ])
            ->willReturn($access);

        $voter = new InventoryVoter($accessRepository);

        // ─────────────────────────────
        // Security token
        // ─────────────────────────────
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );

        // ─────────────────────────────
        // Голосование
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
            InventoryVoter::ACCESS_GRANTED,
            $result
        );
    }
}
