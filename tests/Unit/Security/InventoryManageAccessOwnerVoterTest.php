<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryManageAccessOwnerVoterTest extends TestCase
{
    public function testOwnerCanManageAccess(): void
    {
        $owner = new User();
        $owner->setEmail('owner@test.com');

        $inventory = new Inventory(
            $owner,
            'Owner inventory',
            'Description',
            'Category'
        );

        $voter = new InventoryVoter(
            $this->createMock(\App\Repository\InventoryAccessRepository::class)
        );

        $token = new UsernamePasswordToken($owner, 'main', $owner->getRoles());

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
