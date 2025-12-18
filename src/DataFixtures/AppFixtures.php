<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Inventory;
use App\Entity\InventoryField;
use App\Entity\InventoryItem;
use App\Entity\DiscussionPost;
use App\Entity\DiscussionPostLike;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        /**
         * =========================
         * USERS
         * =========================
         */

        // ADMIN
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setUsername('admin');
        $admin->setAdmin(true);
        $admin->setBlocked(false);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        // USER
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setUsername('user');
        $user->setAdmin(false);
        $user->setBlocked(false);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'user123')
        );
        $manager->persist($user);

        /**
         * =========================
         * INVENTORIES
         * =========================
         */

        /**
         * PUBLIC inventory (ADMIN)
         * — доступен всем
         * — используется в базовых тестах
         */
        $publicInventory = new Inventory(
            $admin,
            'Demo Inventory',
            'Inventory created from fixtures',
            'Demo'
        );
        $publicInventory->setPublic(true);
        $manager->persist($publicInventory);

        /**
         * PRIVATE inventory (ADMIN)
         * — НЕ доступен другим пользователям
         * — КРИТИЧЕСКИ ВАЖНО для InventoryOwnerVoterTest
         */
        $privateInventory = new Inventory(
            $admin,
            'Private Inventory',
            'Private inventory for ACL tests',
            'Private'
        );
        $privateInventory->setPublic(false);
        $manager->persist($privateInventory);

        /**
         * =========================
         * FIELDS
         * =========================
         */

        $field = new InventoryField(
            $publicInventory,
            'TEXT',
            'Serial number',
            1
        );
        $manager->persist($field);

        /**
         * =========================
         * ITEMS
         * =========================
         */

        $item = new InventoryItem(
            $publicInventory,
            $admin,
            'ITEM-001'
        );
        $manager->persist($item);

        /**
         * =========================
         * DISCUSSION
         * =========================
         */

        $post = new DiscussionPost(
            $publicInventory,
            $user,
            'Hello! I am a regular user'
        );
        $manager->persist($post);

        $like = new DiscussionPostLike(
            $user,
            $post
        );
        $manager->persist($like);

        /**
         * =========================
         * FLUSH
         * =========================
         */

        $manager->flush();
    }
}
