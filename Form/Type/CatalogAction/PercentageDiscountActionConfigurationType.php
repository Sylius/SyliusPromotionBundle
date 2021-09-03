<?php

declare(strict_types=1);

namespace Sylius\Bundle\PromotionBundle\Form\Type\CatalogAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;

final class PercentageDiscountActionConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', PercentType::class, [
                'label' => 'sylius.ui.amount',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_catalog_promotion_action_percentage_discount_configuration';
    }
}
