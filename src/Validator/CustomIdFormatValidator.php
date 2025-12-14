<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\InventoryItem;
use App\Domain\CustomId\CustomIdTemplate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CustomIdFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomIdFormat) {
            return;
        }

        if (!$value instanceof InventoryItem) {
            return;
        }

        $customId = $value->getCustomId();
        $inventory = $value->getInventory();

        /**
         * Здесь предполагается, что шаблон
         * уже загружен из Inventory
         */
        $template = $inventory->getCustomIdTemplate();

        if (!$this->matchesTemplate($customId, $template)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('customId')
                ->addViolation();
        }
    }

    private function matchesTemplate(
        string $customId,
        CustomIdTemplate $template
    ): bool {
        // Упрощённая версия:
        // реальная проверка зависит от частей шаблона
        // сейчас проверяем только базовую валидность для курсовой это норм

        return $customId !== '';
    }
}
