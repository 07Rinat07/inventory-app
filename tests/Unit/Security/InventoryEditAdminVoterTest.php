<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryEditAdminVoterTest extends TestCase
{
    public function testAdminCanEditAnyInventory(): void
    {
        // ─────────────────────────────
        // ACL репозиторий не используется (admin)
        // ─────────────────────────────
        $accessRepository = $this->createMock(InventoryAccessRepository::class);

        $voter = new InventoryVoter($accessRepository);

        // ─────────────────────────────
        // Администратор
        // ─────────────────────────────
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setAdmin(true); // важно для ROLE_ADMIN

        // ─────────────────────────────
        // Владелец inventory (не admin)
        // ─────────────────────────────
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // ─────────────────────────────
        // Inventory принадлежит owner
        // ─────────────────────────────
        $inventory = new Inventory(
            $owner,
            'Any inventory',
            'Description',
            'Category'
        );

        // ─────────────────────────────
        // Security token администратора
        // ─────────────────────────────
        $token = new UsernamePasswordToken(
            $admin,
            'main',
            $admin->getRoles()
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
