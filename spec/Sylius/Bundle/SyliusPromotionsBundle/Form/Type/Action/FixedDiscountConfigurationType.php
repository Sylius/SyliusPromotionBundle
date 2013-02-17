<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Sylius\Bundle\PromotionsBundle\Form\Type\Action;

use PHPSpec2\ObjectBehavior;

/**
 * Order total action configuration form type spec.
 *
 * @author Saša Stamenković <umpirsky@gmail.com>
 */
class FixedDiscountConfigurationType extends ObjectBehavior
{
    function it_should_be_initializable()
    {
        $this->shouldHaveType('Sylius\Bundle\PromotionsBundle\Form\Type\Action\FixedDiscountConfigurationType');
    }

    function it_should_be_a_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    /**
     * @param Symfony\Component\Form\FormBuilder $builder
     */
    function it_should_build_form_with_count_field_and_equal_checkbox($builder)
    {
        $builder
            ->add('amount', 'money', ANY_ARGUMENT)
            ->shouldBeCalled()
            ->willReturn($builder)
        ;

        $this->buildForm($builder, array());
    }
}
