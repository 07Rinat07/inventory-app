<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discussion;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DiscussionGuestTest extends WebTestCase
{
    public function testGuestCannotWriteDiscussion(): void
    {
        $client = static::createClient();

        // Пытаемся отправить сообщение в discussion без авторизации
        $client->request(
            'POST',
            '/inventories/1/discussion',
            [
                'message' => 'Guest message',
            ]
        );

        // Гость либо получает 403, либо редирект на login
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [302, 403], true),
            'Guest must not be allowed to write discussion'
        );
    }
}
