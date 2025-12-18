<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AnonymousAccessTest extends WebTestCase
{
    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();

        // Запрашиваем реально защищённый маршрут
        $client->request('GET', '/inventories');

        // Проверяем, что происходит редирект
        $this->assertResponseRedirects('/login');
    }
}
