<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginFailureTest extends WebTestCase
{
    public function testUserCannotLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();

        // 1. Открываем страницу логина
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        // 2. Отправляем форму с НЕверными данными
        $form = $crawler->filter('form')->form([
            '_username' => 'user@test.com',
            '_password' => 'wrong-password',
        ]);

        $client->submit($form);

        // 3. После ошибки логина — редирект обратно на /login
        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        // 4. Пытаемся зайти на РЕАЛЬНО защищённый маршрут
        $client->request('GET', '/inventories');

        // 5. Доступ запрещён → редирект на login
        $this->assertResponseRedirects('/login');
    }
}
