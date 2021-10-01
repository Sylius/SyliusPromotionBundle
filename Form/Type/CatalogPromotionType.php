<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\PromotionBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class CatalogPromotionType extends AbstractResourceType
{
    private string $catalogPromotionTranslationType;

    public function __construct(
        string $dataClass,
        array $validationGroups,
        string $catalogPromotionTranslationType
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->catalogPromotionTranslationType = $catalogPromotionTranslationType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber())
            ->add('name', TextType::class, [
                'label' => 'sylius.form.catalog_promotion.name',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => $this->catalogPromotionTranslationType,
                'label' => 'sylius.form.catalog_promotion.translations',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.form.catalog_promotion.enabled'
            ] )
            ->add('scopes', CollectionType::class, [
                'label' => 'sylius.ui.scopes',
                'entry_type' => CatalogPromotionScopeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('actions', CollectionType::class, [
                'label' => 'sylius.ui.actions',
                'entry_type' => CatalogPromotionActionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_catalog_promotion';
    }
}
