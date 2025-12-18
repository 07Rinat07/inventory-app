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

final class InventoryDiscussionReadAccessVoterTest extends TestCase
{
    public function testUserWithReadAccessCannotWriteDiscussion(): void
    {
        // 1. Owner
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // 2. User with READ access
        $user = new User();
        $user->setEmail('reader@test.com');

        // 3. Inventory
        $inventory = new Inventory(
            $owner,
            'Inventory',
            'Description',
            'Category'
        );

        // 4. ACL READ
        $access = new InventoryAccess(
            $inventory,
            $user,
            'READ'
        );

        // 5. Mock repository
        $repository = $this->createMock(InventoryAccessRepository::class);
        $repository
            ->method('findOneBy')
            ->willReturn($access);

        // 6. Voter
        $voter = new InventoryVoter($repository);

        // 7. Token
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );

        // 8. Vote
        $result = $voter->vote(
            $token,
            $inventory,
            [InventoryVoter::DISCUSSION_WRITE]
        );

        // 9. Assert DENIED
        $this->assertSame(
            InventoryVoter::ACCESS_DENIED,
            $result
        );
    }
}
