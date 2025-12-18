<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LogoutTest extends WebTestCase
{
    public function testUserIsLoggedOutAndCannotAccessProtectedPage(): void
    {
        $client = static::createClient();

        // 1. Логиним пользователя БЕЗ формы
        $client->loginUser(
            $client->getContainer()
                ->get('doctrine')
                ->getRepository(\App\Entity\User::class)
                ->findOneBy(['email' => 'user@test.com'])
        );

        // 2. Проверяем, что защищённая страница доступна
        $client->request('GET', '/inventories');
        $this->assertResponseIsSuccessful();

        // 3. Вызываем logout (БЕЗ followRedirect)
        $client->request('GET', '/logout');

        // logout всегда редиректит
        $this->assertResponseRedirects('/login');

        // 4. Переходим по редиректу
        $client->followRedirect();

        // 5. Теперь защищённая страница должна быть недоступна
        $client->request('GET', '/inventories');
        $this->assertResponseRedirects('/login');
    }
}
