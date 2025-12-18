<?php

namespace App\Tests\Functional\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InventoryOwnerVoterTest extends WebTestCase
{
    /**
     * Пользователь не может получить доступ к чужому inventory
     */
    public function testUserCannotViewForeignInventory(): void
    {
        $client = static::createClient();

        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();

        /** @var User $user */
        $user = $em->getRepository(User::class)
            ->findOneBy(['email' => 'user@test.com']);

        /** @var Inventory $foreignInventory */
        $foreignInventory = $em->getRepository(Inventory::class)
            ->createQueryBuilder('i')
            ->where('i.owner != :user')
            ->andWhere('i.isPublic = false')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();


        $this->assertNotNull(
            $foreignInventory,
            'В фикстурах должен существовать inventory другого пользователя'
        );

        $client->loginUser($user);
        $client->request('GET', '/inventories/' . $foreignInventory->getId());

        $this->assertResponseStatusCodeSame(403);
    }
}
