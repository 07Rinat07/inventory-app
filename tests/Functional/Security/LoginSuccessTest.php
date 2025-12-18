<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginSuccessTest extends WebTestCase
{
    public function testUserCanLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        // 1. Открываем страницу логина
        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();

        // 2. Находим форму по полю username (а не по кнопке!)
        $form = $crawler->filter('form')->form([
            '_username' => 'user@test.com',
            '_password' => 'user123',
        ]);

        // 3. Отправляем форму
        $client->submit($form);

        // 4. Проверяем редирект после логина
        self::assertResponseRedirects('/inventories');

        // 5. Переходим по редиректу
        $client->followRedirect();

        // 6. Проверяем, что пользователь аутентифицирован
        self::assertResponseIsSuccessful();
    }
}
