<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Inventory;
use App\Entity\InventoryAccess;
use App\Entity\User;
use App\Repository\InventoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class InventoryVoter extends Voter
{
    public const VIEW = 'INVENTORY_VIEW';
    public const EDIT = 'INVENTORY_EDIT';
    public const MANAGE_ACCESS = 'INVENTORY_MANAGE_ACCESS';

    public const CREATE_ITEM = 'INVENTORY_CREATE_ITEM';
    public const EDIT_ITEM   = 'INVENTORY_EDIT_ITEM';
    public const DELETE_ITEM = 'INVENTORY_DELETE_ITEM';

    public const DISCUSSION_WRITE = 'DISCUSSION_WRITE';

    public function __construct(
        private readonly InventoryAccessRepository $accessRepository
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Inventory) {
            return false;
        }

        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::MANAGE_ACCESS,
            self::CREATE_ITEM,
            self::EDIT_ITEM,
            self::DELETE_ITEM,
            self::DISCUSSION_WRITE,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Inventory $inventory */
        $inventory = $subject;

        $user = $token->getUser();

        // ─────────────────────────────
        // GUEST (не аутентифицирован)
        // ─────────────────────────────
        // Гость может видеть ТОЛЬКО публичный inventory.
        if (!$user instanceof User) {
            return $attribute === self::VIEW && $inventory->isPublic();
        }

        // ─────────────────────────────
        // ADMIN — полный доступ
        // ─────────────────────────────
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // ─────────────────────────────
        // OWNER — полный доступ
        // ─────────────────────────────
        // Важно: сравниваем сначала объект, затем id (если id уже установлен).
        // Это защищает от ситуации, когда id=null (unit tests / unsaved entities),
        // и "null === null" ошибочно дает владельца любому пользователю.
        $owner = $inventory->getOwner();

        if ($owner === $user) {
            return true;
        }

        $ownerId = $owner?->getId();
        $userId  = $user->getId();

        if ($ownerId !== null && $userId !== null && $ownerId === $userId) {
            return true;
        }

        // ─────────────────────────────
        // PUBLIC даёт ТОЛЬКО VIEW (для не-владельца)
        // ─────────────────────────────
        if ($inventory->isPublic() && $attribute === self::VIEW) {
            return true;
        }

        // ─────────────────────────────
        // ACL (InventoryAccess)
        // ─────────────────────────────
        // READ  -> только VIEW
        // WRITE -> VIEW + CRUD items + DISCUSSION_WRITE
        $access = $this->accessRepository->findOneBy([
            'inventory' => $inventory,
            'user'      => $user,
        ]);

        if (!$access instanceof InventoryAccess) {
            return false;
        }

        return match ($access->getRole()) {
            'READ' => $attribute === self::VIEW,

            'WRITE' => in_array($attribute, [
                self::VIEW,
                self::EDIT,              // ← ВАЖНО: добавили
                self::CREATE_ITEM,
                self::EDIT_ITEM,
                self::DELETE_ITEM,
                self::DISCUSSION_WRITE,
            ], true),

            default => false,
        };

    }
}
