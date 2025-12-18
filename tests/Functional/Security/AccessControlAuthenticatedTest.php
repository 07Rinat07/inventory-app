<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessControlAuthenticatedTest extends WebTestCase
{
    /**
     * Авторизованный пользователь должен иметь доступ
     * к защищённой странице
     */
    public function testAuthenticatedUserCanAccessInventories(): void
    {
        $client = static::createClient();

        // Берём пользователя из фикстур
        $user = self::getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'user@test.com']);

        // Подстраховка, чтобы тест падал понятно
        $this->assertNotNull($user, 'Тестовый пользователь не найден');

        // Логиним пользователя в тестовом клиенте
        $client->loginUser($user);

        // Запрашиваем защищённую страницу
        $client->request('GET', '/inventories');

        // Проверяем, что доступ разрешён
        $this->assertResponseIsSuccessful();
    }
}
