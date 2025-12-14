<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InventoryVoter extends Voter
{
    public const VIEW = 'INVENTORY_VIEW';
    public const EDIT = 'INVENTORY_EDIT';
    public const MANAGE_ACCESS = 'INVENTORY_MANAGE_ACCESS';
    public const CREATE_ITEM = 'INVENTORY_CREATE_ITEM';
    public const EDIT_ITEM = 'INVENTORY_EDIT_ITEM';

    public function __construct(
        private InventoryAccessRepository $accessRepository
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
                self::VIEW,
                self::EDIT,
                self::MANAGE_ACCESS,
                self::CREATE_ITEM,
                self::EDIT_ITEM,
            ], true) && $subject instanceof Inventory;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();

        // гость
        if (!$user instanceof User) {
            return $attribute === self::VIEW;
        }

        // админ
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        /** @var Inventory $inventory */
        $inventory = $subject;

        // владелец
        if ($inventory->getOwner()->getId() === $user->getId()) {
            return true;
        }

        // публичный инвентарь
        if ($inventory->isPublic()) {
            return in_array($attribute, [
                self::VIEW,
                self::CREATE_ITEM,
                self::EDIT_ITEM,
            ], true);
        }

        // ACL
        $access = $this->accessRepository->findOneBy([
            'inventory' => $inventory,
            'user' => $user,
        ]);

        if (!$access instanceof InventoryAccess) {
            return false;
        }

        return match ($access->getRole()) {
            'OWNER' => true,
            'WRITE' => in_array($attribute, [
                self::VIEW,
                self::CREATE_ITEM,
                self::EDIT_ITEM,
            ], true),
            default => false,
        };
    }
}
