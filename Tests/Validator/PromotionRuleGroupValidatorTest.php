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
use Sylius\Bundle\PromotionBundle\Validator\Constraints\PromotionRuleGroup;
use Sylius\Bundle\PromotionBundle\Validator\PromotionRuleGroupValidator;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PromotionRuleGroupValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface&MockObject */
    private MockObject $context;

    private PromotionRuleGroupValidator $promotionRuleGroupValidator;

    /** @var PromotionRuleInterface&MockObject */
    private PromotionRuleInterface $promotionRule;

    /** @var ValidatorInterface&MockObject */
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->promotionRuleGroupValidator = new PromotionRuleGroupValidator([
            'rule_two' => ['Default' => 'Default', 'rule' => 'rule_two'],
        ]);
        $this->promotionRuleGroupValidator->initialize($this->context);
        $this->promotionRule = $this->createMock(PromotionRuleInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testThrowsAnExceptionIfConstraintIsNotAnInstanceOfPromotionRuleGroup(): void
    {
        /** @var Constraint&MockObject $constraint */
        $constraint = $this->createMock(Constraint::class);

        self::expectException(UnexpectedTypeException::class);

        $this->promotionRuleGroupValidator->validate($this->promotionRule, $constraint);
    }

    public function testThrowsAnExceptionIfValueIsNotAnInstanceOfPromotionRule(): void
    {
        self::expectException(UnexpectedValueException::class);

        $this->promotionRuleGroupValidator->validate(new \stdClass(), new PromotionRuleGroup());
    }

    public function testCallsAValidatorWithGroup(): void
    {
        /** @var ContextualValidatorInterface&MockObject $contextualValidator */
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $this->promotionRule->expects(self::once())->method('getType')->willReturn('rule_two');

        $this->context->expects(self::once())->method('getValidator')->willReturn($this->validator);

        $this->validator->expects(self::once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($contextualValidator);

        $contextualValidator->expects(self::once())
            ->method('validate')
            ->with(
                $this->promotionRule,
                null,
                [
                    'Default' => 'Default',
                    'rule' => 'rule_two',
                ],
            )
            ->willReturn($contextualValidator);

        $this->promotionRuleGroupValidator->validate(
            $this->promotionRule,
            new PromotionRuleGroup(['groups' => ['Default', 'test_group']]),
        );
    }

    public function testCallsValidatorWithDefaultGroupsIfNoneProvidedForPromotionActionType(): void
    {
        /** @var ContextualValidatorInterface&MockObject $contextualValidator */
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $this->promotionRule->expects(self::once())->method('getType')->willReturn('rule_one');

        $this->context->expects(self::once())->method('getValidator')->willReturn($this->validator);

        $this->validator->expects(self::once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($contextualValidator);

        $contextualValidator->expects(self::once())
            ->method('validate')
            ->with($this->promotionRule, null, ['Default', 'test_group'])
            ->willReturn($contextualValidator);

        $this->promotionRuleGroupValidator->validate(
            $this->promotionRule,
            new PromotionRuleGroup(['groups' => ['Default', 'test_group']]),
        );
    }
}
