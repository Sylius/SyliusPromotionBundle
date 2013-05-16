<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Sylius\Bundle\PromotionsBundle\Generator;

use PHPSpec2\ObjectBehavior;

/**
 * Promotion coupon generate instruction spec.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class Instruction extends ObjectBehavior
{
    function it_should_be_initializable()
    {
        $this->shouldHaveType('Sylius\Bundle\PromotionsBundle\Generator\Instruction');
    }

    function it_should_have_amount_equal_to_5_by_default()
    {
        $this->getAmount()->shouldReturn(5);
    }

    function its_amount_should_be_mutable()
    {
        $this->setAmount(500);
        $this->getAmount()->shouldReturn(500);
    }

    function it_should_not_have_usage_limit_by_default()
    {
        $this->getUsageLimit()->shouldReturn(null);
    }

    function its_usage_limit_should_be_mutable()
    {
        $this->setUsageLimit(3);
        $this->getUsageLimit()->shouldReturn(3);
    }
}