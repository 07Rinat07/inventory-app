<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

abstract class WebTestCaseBase extends WebTestCase
{
    protected function createClientWithLogin(
        string $email,
        string $password
    ): AbstractBrowser {
        $client = static::createClient();

        // Переходим на страницу логина
        $crawler = $client->request('GET', '/login');

        // Проверяем, что форма логина существует
        self::assertResponseIsSuccessful();

        // Отправляем форму логина
        $form = $crawler->selectButton('Sign in')->form([
            'email'    => $email,
            'password' => $password,
        ]);

        $client->submit($form);

        // После логина должен быть редирект
        self::assertResponseRedirects();

        // Следуем редиректу
        $client->followRedirect();

        return $client;
    }
}
