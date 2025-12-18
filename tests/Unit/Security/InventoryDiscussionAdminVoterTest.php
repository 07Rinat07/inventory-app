<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Inventory;
use App\Entity\User;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InventoryDiscussionAdminVoterTest extends TestCase
{
    public function testAdminCanWriteDiscussion(): void
    {
        // 1. Создаём администратора
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setAdmin(true); // ВАЖНО: делает ROLE_ADMIN

        // 2. Создаём владельца inventory (другой пользователь)
        $owner = new User();
        $owner->setEmail('owner@test.com');

        // 3. Создаём inventory, владельцем является НЕ админ
        $inventory = new Inventory(
            $owner,
            'Foreign inventory',
            'Description',
            'Category'
        );

        // 4. Создаём voter
        // InventoryAccessRepository не нужен, т.к. admin проходит раньше
        $voter = new InventoryVoter(
            $this->createMock(\App\Repository\InventoryAccessRepository::class)
        );

        // 5. Токен администратора
        $token = new UsernamePasswordToken(
            $admin,
            'main',
            $admin->getRoles()
        );

        // 6. Голосование
        $result = $voter->vote(
            $token,
            $inventory,
            [InventoryVoter::DISCUSSION_WRITE]
        );

        // 7. Проверка
        $this->assertSame(
            InventoryVoter::ACCESS_GRANTED,
            $result
        );
    }
}
