<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryDiscussionOwnerVoterTest extends TestCase
{
    public function testOwnerCanWriteDiscussion(): void
    {
        $voter = new InventoryVoter(
            $this->createMock(\App\Repository\InventoryAccessRepository::class)
        );

        $owner = new User();
        $owner->setEmail('owner@test.com');

        $inventory = new Inventory(
            $owner,
            'Owner inventory',
            'Description',
            'Category'
        );

        $token = new UsernamePasswordToken($owner, 'main', $owner->getRoles());

        $result = $voter->vote(
            $token,
            $inventory,
            [InventoryVoter::DISCUSSION_WRITE]
        );

        $this->assertSame(InventoryVoter::ACCESS_GRANTED, $result);
    }
}
