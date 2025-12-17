<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class InventoryItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('customId', TextType::class, [
            'label' => 'Custom ID',
        ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        // ❗ НИЧЕГО не указываем
        // data_class задаётся в контроллере
        $resolver->setDefaults([]);
    }
}
