<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use App\Security\Voter\InventoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class InventoryVoterTest extends TestCase
{
    private InventoryAccessRepository $accessRepository;
    private InventoryVoter $voter;

    protected function setUp(): void
    {
        $this->accessRepository = $this->createMock(InventoryAccessRepository::class);
        $this->voter = new InventoryVoter($this->accessRepository);
    }


    private function setId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }



    private function token(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    private int $userId = 1;

    private function user(bool $admin = false): User
    {
        $user = new User();
        $user->setEmail('test'.$this->userId.'@test.com');
        $user->setUsername('test'.$this->userId);
        $user->setAdmin($admin);
        $user->setBlocked(false);

        $this->setId($user, $this->userId++);
        return $user;
    }

    private function inventory(User $owner, bool $public = false): Inventory
    {
        $inventory = new Inventory(
            $owner,
            'Test inventory',
            'Description',
            'Category'
        );
        $inventory->setPublic($public);

        return $inventory;
    }

    // ─────────────────────────────
    // ADMIN
    // ─────────────────────────────

    public function testAdminHasFullAccess(): void
    {
        $admin = $this->user(admin: true);
        $inventory = $this->inventory($admin);
        $token = $this->token($admin);

        foreach ($this->allAttributes() as $attribute) {
            self::assertTrue(
                $this->voter->vote($token, $inventory, [$attribute]) > 0,
                "Admin should have access to {$attribute}"
            );
        }
    }

    // ─────────────────────────────
    // OWNER
    // ─────────────────────────────

    public function testOwnerHasFullAccess(): void
    {
        $owner = $this->user();
        $inventory = $this->inventory($owner);
        $token = $this->token($owner);

        foreach ($this->allAttributes() as $attribute) {
            self::assertTrue(
                $this->voter->vote($token, $inventory, [$attribute]) > 0
            );
        }
    }

    // ─────────────────────────────
    // PUBLIC INVENTORY
    // ─────────────────────────────

    public function testUserCanViewPublicInventory(): void
    {
        $owner = $this->user();
        $user  = $this->user();

        $inventory = $this->inventory($owner, public: true);
        $token = $this->token($user);

        self::assertTrue(
            $this->voter->vote($token, $inventory, [InventoryVoter::VIEW]) > 0
        );
    }

    public function testUserCannotEditPublicInventory(): void
    {
        $owner = $this->user();
        $user  = $this->user();

        $inventory = $this->inventory($owner, public: true);
        $token = $this->token($user);

        self::assertFalse(
            $this->voter->vote($token, $inventory, [InventoryVoter::EDIT]) > 0
        );
    }

    // ─────────────────────────────
    // ACL: READ
    // ─────────────────────────────

    public function testReadAccessAllowsOnlyView(): void
    {
        $owner = $this->user();
        $user  = $this->user();
        $inventory = $this->inventory($owner);

        $access = new InventoryAccess($inventory, $user, 'READ');

        $this->accessRepository
            ->method('findOneBy')
            ->willReturn($access);

        $token = $this->token($user);

        self::assertTrue(
            $this->voter->vote($token, $inventory, [InventoryVoter::VIEW]) > 0
        );

        self::assertFalse(
            $this->voter->vote($token, $inventory, [InventoryVoter::EDIT]) > 0
        );
    }

    // ─────────────────────────────
    // ACL: WRITE
    // ─────────────────────────────

    public function testWriteAccessAllowsItemManagementAndDiscussion(): void
    {
        $owner = $this->user();
        $user  = $this->user();
        $inventory = $this->inventory($owner);

        $access = new InventoryAccess($inventory, $user, 'WRITE');

        $this->accessRepository
            ->method('findOneBy')
            ->willReturn($access);

        $token = $this->token($user);

        foreach ([
                     InventoryVoter::VIEW,
                     InventoryVoter::CREATE_ITEM,
                     InventoryVoter::EDIT_ITEM,
                     InventoryVoter::DELETE_ITEM,
                     InventoryVoter::DISCUSSION_WRITE,
                 ] as $attribute) {
            self::assertTrue(
                $this->voter->vote($token, $inventory, [$attribute]) > 0
            );
        }

        self::assertFalse(
            $this->voter->vote($token, $inventory, [InventoryVoter::MANAGE_ACCESS]) > 0
        );
    }

    private function allAttributes(): array
    {
        return [
            InventoryVoter::VIEW,
            InventoryVoter::EDIT,
            InventoryVoter::MANAGE_ACCESS,
            InventoryVoter::CREATE_ITEM,
            InventoryVoter::EDIT_ITEM,
            InventoryVoter::DELETE_ITEM,
            InventoryVoter::DISCUSSION_WRITE,
        ];
    }
}
