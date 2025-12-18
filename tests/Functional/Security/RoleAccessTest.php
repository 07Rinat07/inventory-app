<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoleAccessTest extends WebTestCase
{
    /**
     * Обычный пользователь имеет доступ к inventories
     */
    public function testUserCanAccessInventories(): void
    {
        $client = static::createClient();

        $user = self::getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'user@test.com']);

        $client->loginUser($user);
        $client->request('GET', '/inventories');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Гость НЕ имеет доступа к inventories
     */
    public function testGuestCannotAccessInventories(): void
    {
        $client = static::createClient();

        $client->request('GET', '/inventories');

        $this->assertResponseRedirects('/login');
    }
}
