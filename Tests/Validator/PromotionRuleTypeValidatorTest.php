<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\Bundle\PromotionBundle\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\PromotionRuleType;
use Sylius\Bundle\PromotionBundle\Validator\PromotionRuleTypeValidator;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class PromotionRuleTypeValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface&MockObject */
    private MockObject $contextMock;

    private PromotionRuleTypeValidator $promotionRuleTypeValidator;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->promotionRuleTypeValidator = new PromotionRuleTypeValidator(['rule_one' => 'rule_one', 'rule_two' => 'rule_two']);
        $this->promotionRuleTypeValidator->initialize($this->contextMock);
    }

    public function testThrowsAnExceptionIfConstraintIsNotAnInstanceOfPromotionRuleType(): void
    {
        /** @var Constraint&MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        /** @var PromotionRuleInterface&MockObject $promotionRuleMock */
        $promotionRuleMock = $this->createMock(PromotionRuleInterface::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->promotionRuleTypeValidator->validate($promotionRuleMock, $constraintMock);
    }

    public function testThrowsAnExceptionIfValueIsNotAnInstanceOfArray(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->promotionRuleTypeValidator->validate(new stdClass(), new PromotionRuleType());
    }

    public function testAddsViolationIfPromotionRuleHasInvalidType(): void
    {
        /** @var ConstraintViolationBuilderInterface&MockObject $constraintViolationBuilderMock */
        $constraintViolationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        /** @var PromotionRuleInterface&MockObject $promotionRuleMock */
        $promotionRuleMock = $this->createMock(PromotionRuleInterface::class);
        $promotionRuleMock->expects($this->once())->method('getType')->willReturn('wrong_type');
        $this->contextMock->expects($this->once())->method('buildViolation')->with('sylius.promotion_rule.invalid_type')->willReturn($constraintViolationBuilderMock);
        $constraintViolationBuilderMock->expects($this->once())->method('setParameter')->with('{{ available_rule_types }}', 'rule_one, rule_two')->willReturn($constraintViolationBuilderMock);
        $constraintViolationBuilderMock->expects($this->once())->method('atPath')->with('type')->willReturn($constraintViolationBuilderMock);
        $constraintViolationBuilderMock->expects($this->once())->method('addViolation');
        $this->promotionRuleTypeValidator->validate($promotionRuleMock, new PromotionRuleType());
    }
}
