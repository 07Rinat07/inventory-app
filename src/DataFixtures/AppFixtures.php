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
         * ADMIN
         */
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setUsername('admin');
        $admin->setAdmin(true);
        $admin->setBlocked(false);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        /**
         * USER
         */
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
         * INVENTORY (принадлежит ADMIN)
         */
        $inventory = new Inventory(
            $admin,
            'Demo Inventory',
            'Inventory created from fixtures',
            'Demo'
        );
        $inventory->setPublic(true);
        $manager->persist($inventory);

        /**
         * FIELD
         */
        $field = new InventoryField(
            $inventory,
            'TEXT',
            'Serial number',
            1
        );
        $manager->persist($field);

        /**
         * ITEM
         */
        $item = new InventoryItem(
            $inventory,
            $admin,
            'ITEM-001'
        );
        $manager->persist($item);

        /**
         * DISCUSSION POST (от USER)
         */
        $post = new DiscussionPost(
            $inventory,
            $user,
            'Hello! I am a regular user'
        );
        $manager->persist($post);

        /**
         * LIKE (USER лайкает пост)
         */
        $like = new DiscussionPostLike(
            $user,
            $post
        );
        $manager->persist($like);

        $manager->flush();
    }
}
