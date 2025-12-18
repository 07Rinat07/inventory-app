<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Security\Voter\InventoryVoter;
use App\Repository\InventoryAccessRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryManageAccessAdminVoterTest extends TestCase
{
    public function testAdminCanManageAccess(): void
    {
        // Мок репозитория ACL — он не должен вызываться для ADMIN
        $accessRepository = $this->createMock(InventoryAccessRepository::class);

        $voter = new InventoryVoter($accessRepository);

        // ADMIN
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setAdmin(true);

        // Владелец инвентаря (любой, неважно)
        $owner = new User();
        $owner->setEmail('owner@test.com');

        $inventory = new Inventory(
            $owner,
            'Inventory',
            'Description',
            'Category'
        );

        $token = new UsernamePasswordToken(
            $admin,
            'main',
            $admin->getRoles()
        );

        $result = $voter->vote(
            $token,
            $inventory,
            [InventoryVoter::MANAGE_ACCESS]
        );

        $this->assertSame(
            InventoryVoter::ACCESS_GRANTED,
            $result
        );
    }
}
