<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class InventoryDiscussionGuestVoterTest extends TestCase
{
    public function testGuestCannotWriteDiscussion(): void
    {
        // 1. Owner
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // 2. Inventory (public — но писать нельзя)
        $inventory = new Inventory(
            $owner,
            'Inventory',
            'Description',
            'Category'
        );
        $inventory->setPublic(true);

        // 3. Mock InventoryAccessRepository
        $repository = $this->createMock(InventoryAccessRepository::class);

        // 4. Voter
        $voter = new InventoryVoter($repository);

        // 5. MOCK token → guest (getUser() = null)
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        // 6. Vote
        $result = $voter->vote(
            $token,
            $inventory,
            [InventoryVoter::DISCUSSION_WRITE]
        );

        // 7. Assert
        $this->assertSame(
            InventoryVoter::ACCESS_DENIED,
            $result
        );
    }
}
