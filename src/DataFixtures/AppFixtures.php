<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Entity\InventoryField;
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
        // === ADMIN USER ===
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setUsername('admin');
        $admin->setAdmin(true);
        $admin->setBlocked(false);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );

        $manager->persist($admin);

        // === INVENTORY ===
        $inventory = new Inventory(
            $admin,
            'Demo Inventory',
            'Inventory created from fixtures',
            'Demo'
        );

        $inventory->setPublic(true);
        $manager->persist($inventory);

        // === INVENTORY FIELD ===
        $field = new InventoryField(
            $inventory,
            'TEXT',
            'Serial number',
            1
        );
        $manager->persist($field);

        // === INVENTORY ITEM ===
        $item = new InventoryItem(
            $inventory,
            $admin,
            'ITEM-001'
        );
        $manager->persist($item);

        // === DISCUSSION POST ===
        $post = new DiscussionPost(
            $inventory,
            $admin,
            'First discussion message'
        );
        $manager->persist($post);

        // === LIKE ===
        $like = new DiscussionPostLike(
            $admin,
            $post
        );
        $manager->persist($like);

        $manager->flush();
    }
}
