<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessControlTest extends WebTestCase
{
    /**
     * Гость не должен иметь доступ к защищённой странице
     * и должен быть перенаправлен на страницу логина
     */
    public function testGuestCannotAccessInventories(): void
    {
        // Создаём HTTP-клиент без аутентификации (гость)
        $client = static::createClient();

        // Пытаемся открыть защищённый маршрут
        $client->request('GET', '/inventories');

        // Проверяем, что ответ — редирект (302)
        $this->assertResponseRedirects(
            '/login',
            302,
            'Гость должен быть перенаправлен на /login'
        );
    }
}
