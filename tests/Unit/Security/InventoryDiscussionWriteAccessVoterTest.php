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

final class InventoryDiscussionWriteAccessVoterTest extends TestCase
{
    public function testUserWithWriteAccessCanWriteDiscussion(): void
    {
        // 1. Владелец inventory
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // 2. Пользователь с WRITE-доступом
        $user = new User();
        $user->setEmail('writer@test.com');

        // 3. Inventory
        $inventory = new Inventory(
            $owner,
            'Inventory',
            'Description',
            'Category'
        );

        // 4. ACL WRITE
        $access = new InventoryAccess(
            $inventory,
            $user,
            'WRITE'
        );

        // 5. Мокаем репозиторий
        $repository = $this->createMock(InventoryAccessRepository::class);
        $repository
            ->method('findOneBy')
            ->willReturn($access);

        // 6. Voter
        $voter = new InventoryVoter($repository);

        // 7. Security token
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );

        // 8. Голосование
        $result = $voter->vote(
            $token,
            $inventory,
            [InventoryVoter::DISCUSSION_WRITE]
        );

        // 9. Проверка
        $this->assertSame(
            InventoryVoter::ACCESS_GRANTED,
            $result
        );
    }
}
