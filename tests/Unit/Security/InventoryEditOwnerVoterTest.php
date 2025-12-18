<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryEditOwnerVoterTest extends TestCase
{
    public function testOwnerCanEditInventory(): void
    {
        // ─────────────────────────────
        // Mock репозитория (ACL не используется для owner)
        // ─────────────────────────────
        $accessRepository = $this->createMock(InventoryAccessRepository::class);

        $voter = new InventoryVoter($accessRepository);

        // ─────────────────────────────
        // Пользователь — владелец
        // ─────────────────────────────
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // ─────────────────────────────
        // Inventory, принадлежащий owner
        // ─────────────────────────────
        $inventory = new Inventory(
            $owner,
            'Owner inventory',
            'Description',
            'Category'
        );

        // ─────────────────────────────
        // Security token
        // ─────────────────────────────
        $token = new UsernamePasswordToken(
            $owner,
            'main',
            $owner->getRoles()
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
            InventoryVoter::ACCESS_GRANTED,
            $result
        );
    }
}
